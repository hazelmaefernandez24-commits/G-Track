<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class StudentDetail extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'student_id',
        'batch',
        'group',
        'student_number',
        'training_code',
        'created_at',
        'updated_at'
    ];

    public static function get_all_with_academic($batch = null, $group = null, $date = null, $status = null, $fullname = null)
    {
        $query = self::query()
            ->with(['academic', 'user'])
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            });

        if ($date) {
            $query->with(['academic' => function ($q) use ($date) {
                $q->where('academic_date', $date);
            }]);
        }

        if ($batch) {
            $query->where('batch', $batch);
        }

        if ($group) {
            $query->where('group', $group);
        }

        if ($status) {
            switch ($status) {
                case 'not_log_out':
                    $query->where(function ($q) {
                        $q->whereHas('academic', function ($qa) {
                            $qa->whereNull('time_out');
                        })
                        ->orDoesntHave('academic');
                    });
                    break;

                case 'not_log_in':
                    $query->where(function ($q) {
                        $q->whereHas('academic', function ($qa) {
                            $qa->whereNull('time_in');
                        })
                        ->orDoesntHave('academic');
                    });
                    break;

                case 'not_logged':
                    $query->where(function ($q) {
                        $q->whereHas('academic', function ($qa) {
                            $qa->whereNull('time_in')
                            ->whereNull('time_out');
                        })
                        ->orDoesntHave('academic');
                    });
                    break;

                case 'late':
                    $query->whereHas('academic', function ($q) {
                        $q->where(function ($qa) {
                            $qa->where('time_in_remark', 'Late')
                            ->orWhere('time_out_remark', 'Late');
                        });
                    });
                    break;

                case 'absent':
                    $query->whereHas('academic', function ($q) {
                        $q->where(function ($qa) {
                            $qa->where('time_in_remark', 'Absent')
                            ->orWhere('time_out_remark', 'Absent');
                        });
                    });
                    break;
            }
        }

        if ($fullname) {
            $query->whereHas('user', function ($q) use ($fullname) {
                $q->whereRaw("CONCAT(user_fname, ' ', user_lname) LIKE ?", ["%$fullname%"]);
            });
        }

        $query->orderBy(PNUser::select('user_fname')
            ->whereColumn('pnph_users.user_id', 'student_details.user_id'));

        return $query->get();
    }

    public static function get_all_with_goingout($gender = null, $date = null, $status = null, $fullname = null, $count = null, $batch = null, $group = null)
    {
        $query = self::query()
            ->with(['goingOut', 'user'])
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            });

        if ($gender) {
            $query->whereHas('user', function ($qa) use ($gender) {
                    $qa->where('gender', $gender);
                });
        }

        if ($batch) {
            $query->where('batch', $batch);
        }

        if ($group) {
            $query->where('group', $group);
        }

        if ($count) {
            $query->whereHas('goingOut', function ($qa) use ($count) {
                    $qa->where('session_number', $count);
                });
        }

        if ($date) {
            $query->with(['goingOut' => function ($q) use ($date) {
                $q->where('going_out_date', $date);
            }]);
        }

        if ($status) {
            switch ($status) {
                case 'not_log_out':
                    $query->where(function ($q) {
                        $q->whereHas('goingOut', function ($qa) {
                            $qa->whereNull('time_out');
                        })
                        ->orDoesntHave('goingOut');
                    });
                    break;

                case 'not_log_in':
                    $query->where(function ($q) {
                        $q->whereHas('goingOut', function ($qa) {
                            $qa->whereNull('time_in');
                        })
                        ->orDoesntHave('goingOut');
                    });
                    break;

                case 'not_logged':
                    $query->where(function ($q) {
                        $q->whereHas('goingOut', function ($qa) {
                            $qa->whereNull('time_in')
                            ->whereNull('time_out');
                        })
                        ->orDoesntHave('goingOut');
                    });
                    break;

                case 'late':
                    $query->whereHas('goingOut', function ($q) {
                        $q->where(function ($qa) {
                            $qa->where('time_in_remark', 'Late')
                            ->orWhere('time_out_remark', 'Late');
                        });
                    });
                    break;
            }
        }

        if ($fullname) {
            $query->whereHas('user', function ($q) use ($fullname) {
                $q->whereRaw("CONCAT(user_fname, ' ', user_lname) LIKE ?", ["%$fullname%"]);
            });
        }

        $query->orderBy(PNUser::select('user_fname')
            ->whereColumn('pnph_users.user_id', 'student_details.user_id'));

        return $query->get();
    }

    public static function get_all_with_intern($date = null, $status = null, $company = null, $fullname = null, $batch = null, $group = null)
    {
        $query = self::query()
            ->with(['intern_log', 'user', 'intern_schedule'])
            ->whereHas('user', function ($q) {
                $q->where('status', true);
            });

        if ($date) {
            $query->with(['intern_log' => function ($q) use ($date) {
                $q->where('date', $date);
            }]);
        }

        if ($batch) {
            $query->where('batch', $batch);
        }

        if ($group) {
            $query->where('group', $group);
        }

        if ($company) {
            $query->where(function ($q) use ($company) {
                $q->whereHas('intern_schedule', function ($qa) use ($company) {
                    $qa->where('company', $company);
                })
                ->orDoesntHave('intern_log');
            });
        }

        if ($status) {
            switch ($status) {
                case 'not_log_out':
                    $query->where(function ($q) {
                        $q->whereHas('intern_log', function ($qa) {
                            $qa->whereNull('time_out');
                        })
                        ->orDoesntHave('intern_log');
                    });
                    break;

                case 'not_log_in':
                    $query->where(function ($q) {
                        $q->whereHas('intern_log', function ($qa) {
                            $qa->whereNull('time_in');
                        })
                        ->orDoesntHave('intern_log');
                    });
                    break;

                case 'not_logged':
                    $query->where(function ($q) {
                        $q->whereHas('intern_log', function ($qa) {
                            $qa->whereNull('time_in')
                            ->whereNull('time_out');
                        })
                        ->orDoesntHave('intern_log');
                    });
                    break;

                case 'late':
                    $query->whereHas('intern_log', function ($q) {
                        $q->where(function ($qa) {
                            $qa->where('time_in_remark', 'Late')
                            ->orWhere('time_out_remark', 'Late');
                        });
                    });
                    break;

                case 'absent':
                    $query->whereHas('intern_log', function ($q) {
                        $q->where(function ($qa) {
                            $qa->where('time_in_remark', 'Absent')
                            ->orWhere('time_out_remark', 'Absent');
                        });
                    });
                    break;
            }
        }

        if ($fullname) {
            $query->whereHas('user', function ($q) use ($fullname) {
                $q->whereRaw("CONCAT(user_fname, ' ', user_lname) LIKE ?", ["%$fullname%"]);
            });
        }

        $query->orderBy(PNUser::select('user_fname')
            ->whereColumn('pnph_users.user_id', 'student_details.user_id'));

        return $query;
    }

    public static function get_all_with_goinghome($batch = null, $group = null, $type = null, $date_time_out = null, $date_time_in = null, $status = null, $fullname = null)
    {
        $query = self::query()
            ->with(['going_home_log', 'user'])
            ->whereHas('user', function ($q) {
                $q->where('status', true);
            });

        if ($batch) {
            $query->where('batch', $batch);
        }

        if ($group) {
            $query->where('group', $group);
        }

        if ($date_time_in || $date_time_out) {
            $query->with(['going_home_log' => function ($q) use ($date_time_in, $date_time_out) {
                if ($date_time_in && $date_time_out) {
                    $q->whereBetween('date_time_out', [$date_time_in, $date_time_out])
                    ->orWhereBetween('date_time_in', [$date_time_in, $date_time_out]);
                } elseif ($date_time_in) {
                    $q->whereDate('date_time_in', $date_time_in);
                } elseif ($date_time_out) {
                    $q->whereDate('date_time_out', $date_time_out);
                }
            }]);
        }


        // Filter by type (if exists in going_home_log)
        if ($type) {
            $query->whereHas('going_home_log', function ($qa) use ($type) {
                $qa->where('schedule_type', $type);
            })
            ->orDoesntHave('going_home_log');
        }

        // Filter by status
        if ($status) {
            switch ($status) {
                case 'not_log_out':
                    $query->where(function ($q) {
                        $q->whereHas('going_home_log', function ($qa) {
                            $qa->whereNull('time_out');
                        })
                        ->orDoesntHave('going_home_log');
                    });
                    break;

                case 'not_log_in':
                    $query->where(function ($q) {
                        $q->whereHas('going_home_log', function ($qa) {
                            $qa->whereNull('time_in');
                        })
                        ->orDoesntHave('going_home_log');
                    });
                    break;

                case 'not_logged':
                    $query->where(function ($q) {
                        $q->whereHas('going_home_log', function ($qa) {
                            $qa->whereNull('time_in')
                            ->whereNull('time_out');
                        })
                        ->orDoesntHave('going_home_log');
                    });
                    break;

                case 'late':
                    $query->whereHas('going_home_log', function ($qa) {
                        $qa->where(function ($sub) {
                            $sub->where('time_in_remarks', 'Late')
                                ->orWhere('time_out_remarks', 'Late');
                        });
                    });
                    break;
            }
        }

        // Filter by full name
        if ($fullname) {
            $query->whereHas('user', function ($q) use ($fullname) {
                $q->whereRaw("CONCAT(user_fname, ' ', user_lname) LIKE ?", ["%$fullname%"]);
            });
        }

        $query->orderBy(PNUser::select('user_fname')
        ->whereColumn('pnph_users.user_id', 'student_details.user_id'));

        return $query;
    }

    public static function get_student($student_id)
    {
        return self::where('student_id', $student_id)->first();
    }

    public function user()
    {
        return $this->belongsTo(PNUser::class, 'user_id', 'user_id');
    }

    public function visitor()
    {
        return $this->hasOne(Visitor::class, 'student_id', 'student_id');
    }

    public function academic()
    {
        return $this->hasOne(Academic::class, 'student_id', 'student_id');
    }

    public function goingOut()
    {
        return $this->hasOne(Going_out::class, 'student_id', 'student_id');
    }

    public function going_home_log()
    {
        return $this->hasOne(GoingHomeModel::class, 'student_id', 'student_id');
    }

    public function intern_log()
    {
        return $this->hasOne(InternLogModel::class, 'student_id', 'student_id');
    }

    public function intern_schedule()
    {
        return $this->hasOne(InternshipSchedule::class, 'student_id', 'student_id');
    }

    public static function getAcademicReport($month = null, $batch = null, $group = null)
    {
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $query = self::query()
            ->with(['user' => fn($q) => $q->where('status', 'active')])
            ->select('student_details.*')
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remark = "Late" AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remark = "Late" AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM academics
                WHERE academics.student_id = student_details.student_id
                AND YEAR(academic_date) = ? AND MONTH(academic_date) = ?
            ) as total_late', [$year, $monthNum])
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remark = "Early" AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remark = "Early" AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM academics
                WHERE academics.student_id = student_details.student_id
                AND YEAR(academic_date) = ? AND MONTH(academic_date) = ?
            ) as total_early', [$year, $monthNum])
            ->selectRaw('(
                SELECT COUNT(*)
                FROM academics
                WHERE academics.student_id = student_details.student_id
                AND YEAR(academic_date) = ? AND MONTH(academic_date) = ?
                AND time_in_remark = "Absent"
            ) as total_absent', [$year, $monthNum])
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remark IN ("Late", "Early", "Absent") AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remark IN ("Late", "Early") AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM academics
                WHERE academics.student_id = student_details.student_id
                AND YEAR(academic_date) = ? AND MONTH(academic_date) = ?
            ) as total_violations', [$year, $monthNum]);

        if ($batch) {
            $query->where('batch', $batch);
        }

        if ($group) {
            $query->where('group', $group);
        }

        return $query->orderByDesc('total_violations');
    }

    public static function getLeisureReport($month = null, $batch = null, $group = null)
    {
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $query = self::query()
            ->with(['user' => fn($q) => $q->where('status', 'active')])
            ->select('student_details.*')
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remark = "Late" AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remark = "Late" AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM going_outs
                WHERE going_outs.student_id = student_details.student_id
                AND YEAR(going_out_date) = ? AND MONTH(going_out_date) = ?
            ) as total_late', [$year, $monthNum])
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remark = "Early" AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remark = "Early" AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM going_outs
                WHERE going_outs.student_id = student_details.student_id
                AND YEAR(going_out_date) = ? AND MONTH(going_out_date) = ?
            ) as total_early', [$year, $monthNum])
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remark IN ("Late", "Early") AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remark IN ("Late", "Early") AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM going_outs
                WHERE going_outs.student_id = student_details.student_id
                AND YEAR(going_out_date) = ? AND MONTH(going_out_date) = ?
            ) as total_violations', [$year, $monthNum]);

        if ($batch) {
            $query->where('batch', $batch);
        }

        if ($group) {
            $query->where('group', $group);
        }

        return $query->orderByDesc('total_violations');
    }

    public static function getInternReport($month = null, $batch = null, $group = null)
    {
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $query = self::query()
            ->with(['user' => fn($q) => $q->where('status', 'active')])
            ->select('student_details.*')
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remark = "Late" AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remark = "Late" AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM intern_log
                WHERE intern_log.student_id = student_details.student_id
                AND YEAR(date) = ? AND MONTH(date) = ?
            ) as total_late', [$year, $monthNum])
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remark = "Early" AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remark = "Early" AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM intern_log
                WHERE intern_log.student_id = student_details.student_id
                AND YEAR(date) = ? AND MONTH(date) = ?
            ) as total_early', [$year, $monthNum])
            ->selectRaw('(
                SELECT COUNT(*)
                FROM intern_log
                WHERE intern_log.student_id = student_details.student_id
                AND YEAR(date) = ? AND MONTH(date) = ?
                AND time_in_remark = "Absent"
                AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL)
            ) as total_absent', [$year, $monthNum])
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remark IN ("Late", "Early", "Absent") AND (educator_consideration = "Not Excused" OR educator_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remark IN ("Late", "Early") AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM intern_log
                WHERE intern_log.student_id = student_details.student_id
                AND YEAR(date) = ? AND MONTH(date) = ?
            ) as total_violations', [$year, $monthNum]);

        if ($batch) {
            $query->where('batch', $batch);
        }

        if ($group) {
            $query->where('group', $group);
        }

        return $query->orderByDesc('total_violations');
    }

    public static function getGoingHomeReport($month = null, $batch = null, $group = null)
    {
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $query = self::query()
            ->with(['user' => fn($q) => $q->where('status', 'active')])
            ->select('student_details.*')
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remarks = "Late" AND (time_in_consideration = "Not Excused" OR time_in_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remarks = "Late" AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM going_home
                WHERE going_home.student_id = student_details.student_id
                AND (
                    (YEAR(date_time_out) = ? AND MONTH(date_time_out) = ?) OR
                    (YEAR(date_time_in) = ? AND MONTH(date_time_in) = ?)
                )
            ) as total_late', [$year, $monthNum, $year, $monthNum])
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remarks = "Early" AND (time_in_consideration = "Not Excused" OR time_in_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remarks = "Early" AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM going_home
                WHERE going_home.student_id = student_details.student_id
                AND (
                    (YEAR(date_time_out) = ? AND MONTH(date_time_out) = ?) OR
                    (YEAR(date_time_in) = ? AND MONTH(date_time_in) = ?)
                )
            ) as total_early', [$year, $monthNum, $year, $monthNum])
            ->selectRaw('(
                SELECT COUNT(*)
                FROM going_home
                WHERE going_home.student_id = student_details.student_id
                AND (
                    (YEAR(date_time_out) = ? AND MONTH(date_time_out) = ?) OR
                    (YEAR(date_time_in) = ? AND MONTH(date_time_in) = ?)
                )
                AND time_in_remarks = "Absent"
                AND (time_in_consideration = "Not Excused" OR time_in_consideration IS NULL)
            ) as total_absent', [$year, $monthNum, $year, $monthNum])
            ->selectRaw('(
                SELECT SUM(
                    (CASE WHEN time_in_remarks IN ("Late", "Early", "Absent") AND (time_in_consideration = "Not Excused" OR time_in_consideration IS NULL) THEN 1 ELSE 0 END) +
                    (CASE WHEN time_out_remarks IN ("Late", "Early") AND (time_out_consideration = "Not Excused" OR time_out_consideration IS NULL) THEN 1 ELSE 0 END)
                )
                FROM going_home
                WHERE going_home.student_id = student_details.student_id
                AND (
                    (YEAR(date_time_out) = ? AND MONTH(date_time_out) = ?) OR
                    (YEAR(date_time_in) = ? AND MONTH(date_time_in) = ?)
                )
            ) as total_violations', [$year, $monthNum, $year, $monthNum]);

        if ($batch) {
            $query->where('batch', $batch);
        }

        if ($group) {
            $query->where('group', $group);
        }

        return $query->orderByDesc('total_violations');
    }
}
