<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Grade Submission Available</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #22bbea;
        }
        .header h1 {
            color: #22bbea;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .submission-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #22bbea;
        }
        .submission-details h3 {
            color: #22bbea;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .action-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #e8f4fd;
            border-radius: 8px;
        }
        .action-button {
            display: inline-block;
            background-color: #22bbea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 15px;
            transition: background-color 0.3s;
        }
        .action-button:hover {
            background-color: #1a9bc8;
        }
        .important-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .important-note strong {
            color: #856404;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>📚 Grade Submission Available</h1>
        </div>

        <div class="content">
            <div class="greeting">
                Hello {{ $student->user_fname }} {{ $student->user_lname }},
            </div>

            <p>A new grade submission has been created for your class. You are required to submit your grades for the following:</p>

            <div class="submission-details">
                <h3>📋 Submission Details</h3>
                <div class="detail-row">
                    <span class="detail-label">School:</span>
                    <span class="detail-value">{{ $gradeSubmission->school->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Class:</span>
                    <span class="detail-value">{{ $gradeSubmission->classModel->class_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Semester:</span>
                    <span class="detail-value">{{ $gradeSubmission->semester }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Term:</span>
                    <span class="detail-value">{{ ucfirst($gradeSubmission->term) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Academic Year:</span>
                    <span class="detail-value">{{ $gradeSubmission->academic_year }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Subjects:</span>
                    <span class="detail-value">{{ $gradeSubmission->subjects->count() }} subject(s)</span>
                </div>
            </div>

            <div class="important-note">
                <strong>⚠️ Important:</strong> Please log in to your student portal and submit your grades as soon as possible. Late submissions may affect your academic standing.
            </div>

            <div class="action-section">
                <p><strong>Ready to submit your grades?</strong></p>
                <p>Click the button below to access your student dashboard:</p>
                <a href="{{ url('/student/dashboard') }}" class="action-button">
                    🎓 Go to Student Dashboard
                </a>
            </div>

            <p>If you have any questions or need assistance, please contact your training coordinator or educator.</p>
        </div>

        <div class="footer">
            <p>This is an automated notification from the PNPH Grade Management System.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
