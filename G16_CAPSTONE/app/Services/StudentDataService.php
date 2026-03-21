<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StudentDataService
{
    /**
     * Extract student data from Login folder's DatabaseSeeder.php
     *
     * @return array
     */
    public function getStudentDataFromLoginSeeder()
    {        
        $loginSeederPath = base_path('..\\Login\\database\\seeders\\DatabaseSeeder.php');
        $logifySeederPath = base_path('..\\Logify\\database\\seeders\\DatabaseSeeder.php');
        
        // Use the first seeder file that exists
        $seederPath = File::exists($loginSeederPath) ? $loginSeederPath : $logifySeederPath;
        
        if (!File::exists($seederPath)) {
            throw new \Exception("Could not find Login or Logify DatabaseSeeder.php file");
        }
        
        $seederContent = File::get($seederPath);
        
        // Extract student data from the seeder file
        $students = $this->parseStudentDataFromSeeder($seederContent);
        
        return $students;
    }
    
    /**
     * Parse student data from seeder content
     *
     * @param string $seederContent
     * @return array
     */
    private function parseStudentDataFromSeeder($seederContent)
    {        
        $students = [
            '2025' => [
                'male' => [],
                'female' => []
            ],
            '2026' => [
                'male' => [],
                'female' => []
            ]
        ];
        
        // Extract PNUser::create blocks
        preg_match_all('/PNUser::create\(\[([^\]]+)\]\);/s', $seederContent, $matches);
        
        if (empty($matches[1])) {
            // Try with User::create if PNUser::create is not found
            preg_match_all('/User::create\(\[([^\]]+)\]\);/s', $seederContent, $matches);
        }
        
        if (empty($matches[1])) {
            throw new \Exception("Could not find user creation blocks in seeder file");
        }
        
        $userBlocks = $matches[1];
        $userIds = [];
        $userData = [];
        
        // Extract user data
        foreach ($userBlocks as $block) {
            // Only process student records
            if (strpos($block, "'user_role' => 'student'") === false) {
                continue;
            }
            
            // Extract user ID
            preg_match("/'user_id'\s*=>\s*(\d+)/", $block, $idMatch);
            if (empty($idMatch[1])) {
                continue;
            }
            
            $userId = $idMatch[1];
            
            // Extract first name
            preg_match("/'user_fname'\s*=>\s*'([^']+)'/", $block, $fnameMatch);
            $firstName = $fnameMatch[1] ?? '';
            
            // Extract last name
            preg_match("/'user_lname'\s*=>\s*'([^']+)'/", $block, $lnameMatch);
            $lastName = $lnameMatch[1] ?? '';
            
            // Extract gender
            preg_match("/'gender'\s*=>\s*'([^']+)'/", $block, $genderMatch);
            $gender = $genderMatch[1] ?? '';
            
            $userIds[] = $userId;
            $userData[$userId] = [
                'id' => $userId,
                'name' => "$lastName, $firstName",
                'gender' => $gender === 'M' ? 'Male' : 'Female'
            ];
        }
        
        // Now extract StudentDetails blocks to get batch information
        preg_match_all('/StudentDetails::create\(\[([^\]]+)\]\);/s', $seederContent, $studentMatches);
        
        if (empty($studentMatches[1])) {
            // Try with StudentDetail if StudentDetails is not found
            preg_match_all('/StudentDetail::create\(\[([^\]]+)\]\);/s', $seederContent, $studentMatches);
        }
        
        if (!empty($studentMatches[1])) {
            $studentBlocks = $studentMatches[1];
            
            foreach ($studentBlocks as $block) {
                // Extract user ID
                preg_match("/'user_id'\s*=>\s*(\d+)/", $block, $idMatch);
                if (empty($idMatch[1])) {
                    continue;
                }
                
                $userId = $idMatch[1];
                
                // Extract batch
                preg_match("/'batch'\s*=>\s*'(\d+)'/", $block, $batchMatch);
                $batch = $batchMatch[1] ?? '';
                
                // Only process batches we're interested in (2025, 2026)
                if ($batch !== '2025' && $batch !== '2026') {
                    continue;
                }
                
                // If we have user data for this ID
                if (isset($userData[$userId])) {
                    $gender = $userData[$userId]['gender'] === 'Male' ? 'male' : 'female';
                    $students[$batch][$gender][] = $userData[$userId]['name'];
                }
            }
        }
        
        // If we couldn't find batch information in StudentDetails, use the GenTaskSeeder data as fallback
        if (empty($students['2025']['male']) && empty($students['2025']['female']) && 
            empty($students['2026']['male']) && empty($students['2026']['female'])) {
            
            // Get data from GenTaskSeeder as fallback
            $genTaskSeederPath = base_path('database\\seeders\\GenTaskSeeder.php');
            if (File::exists($genTaskSeederPath)) {
                $genTaskContent = File::get($genTaskSeederPath);
                
                // Extract batch arrays
                preg_match('/\$batch2025_girls\s*=\s*\[([^\]]+)\]/s', $genTaskContent, $girls2025Match);
                preg_match('/\$batch2025_boys\s*=\s*\[([^\]]+)\]/s', $genTaskContent, $boys2025Match);
                preg_match('/\$batch2026_girls\s*=\s*\[([^\]]+)\]/s', $genTaskContent, $girls2026Match);
                preg_match('/\$batch2026_boys\s*=\s*\[([^\]]+)\]/s', $genTaskContent, $boys2026Match);
                
                if (!empty($girls2025Match[1])) {
                    $students['2025']['female'] = array_map('trim', explode(',', str_replace(["'"], '', $girls2025Match[1])));
                }
                
                if (!empty($boys2025Match[1])) {
                    $students['2025']['male'] = array_map('trim', explode(',', str_replace(["'"], '', $boys2025Match[1])));
                }
                
                if (!empty($girls2026Match[1])) {
                    $students['2026']['female'] = array_map('trim', explode(',', str_replace(["'"], '', $girls2026Match[1])));
                }
                
                if (!empty($boys2026Match[1])) {
                    $students['2026']['male'] = array_map('trim', explode(',', str_replace(["'"], '', $boys2026Match[1])));
                }
            }
        }
        
        return $students;
    }
}