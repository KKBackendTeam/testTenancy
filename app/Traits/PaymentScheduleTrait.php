<?php

namespace App\Traits;
use Carbon\Carbon;
use App\Models\PaymentSchedule;

trait PaymentScheduleTrait
{
    public function createPaymentSchedules($applicant, $tenancy, $start_date, $end_date)
    {
        $payment_schedules = [];

        if ($this->shouldCreateMonthlyPayments($applicant)) {
            $this->createMonthlyPayments($applicant, $tenancy, $start_date, $end_date, $payment_schedules);
        }

        if ($this->shouldCreateQuarterlyPayments($applicant)) {
            $this->createQuarterlyPayments($applicant, $tenancy, $start_date, $end_date, $payment_schedules);
        }

        if ($this->shouldCreateSinglePayment($applicant)) {
            $this->createSinglePayment($applicant, $tenancy, $start_date, $end_date, $payment_schedules);
        }

        $this->savePaymentSchedules($payment_schedules, $applicant, $tenancy);
    }

    public function shouldCreateMonthlyPayments($applicant)
    {
        return (
            ($applicant->level_1 === 1 && $applicant->level_2 === 1 && $applicant->level_3 === 1 && $applicant->level_4 === 0) ||
            ($applicant->level_1 === 2 && $applicant->level_2 === 1 && $applicant->level_3 === 0 && $applicant->level_4 === 0) ||
            ($applicant->level_1 === 2 && $applicant->level_2 === 2 && $applicant->level_3 === 1 && $applicant->level_4 === 0)
        );
    }

    public function shouldCreateQuarterlyPayments($applicant)
    {
        return (
            ($applicant->level_1 === 1 && $applicant->level_2 === 1 && $applicant->level_3 === 2 && $applicant->level_4 === 1) ||
            ($applicant->level_1 === 2 && $applicant->level_2 === 2 && $applicant->level_3 === 2 && $applicant->level_4 === 1) ||
            ($applicant->level_1 === 3 && $applicant->level_2 === 1 && $applicant->level_3 === 1 && $applicant->level_4 === 0)
        );
    }

    public function shouldCreateSinglePayment($applicant)
    {
        return ($applicant->level_1 === 3 && $applicant->level_2 === 2 && $applicant->level_3 === 1 && $applicant->level_4 === 0);
    }

    public function createMonthlyPayments($applicant, $tenancy, $start_date, $end_date, &$payment_schedules)
    {
        $payment_schedules = [];

        $noOfApplicant = $tenancy->applicants()->count();
        $formatted_amount = $tenancy->total_rent / $noOfApplicant;
        $monthly_amount_numeric = number_format($formatted_amount, 2);
        $monthly_amount = (float) str_replace(',', '', $monthly_amount_numeric);
        $days_in_month = $start_date->copy()->endOfMonth()->day;
        $day_difference = $start_date->copy()->endOfMonth()->diffInDays($start_date);
        $days_remaining = $day_difference + 1;
        $payment_for_partial_month = number_format(($monthly_amount / $days_in_month) * $days_remaining, 2);
        $payment_partial_month = (float) str_replace(',', '', $payment_for_partial_month);

        $first_payment_date = $start_date->copy()->subDays(7);

        if ($start_date->day > 1 && $end_date->day == $end_date->copy()->endOfMonth()->day) {
            if ($payment_partial_month < ($monthly_amount / 2)) {
                $combined_rent = $payment_partial_month + $monthly_amount;
                $payment_schedules[] = [
                    'date' => $first_payment_date->copy(),
                    'amount' => $combined_rent,
                ];
            } elseif ($payment_partial_month >= ($monthly_amount / 2)) {
                $payment_schedules[] = [
                    'date' => $first_payment_date->copy(),
                    'amount' => $payment_partial_month,
                ];
            }
        } else {
            $payment_schedules[] = [
                'date' => $first_payment_date->copy(),
                'amount' => $monthly_amount_numeric,
            ];
        }
        $generated_dates = [];
        $current_date = $start_date->copy();
        $second_payment_date_created = false;
        while ($current_date->copy()->addMonthNoOverflow()->startOfMonth()->lte($end_date)) {
            $current_date = $current_date->copy()->addMonthNoOverflow();

            if ($current_date->lte($end_date)) {
                if(($start_date->day == 1) || ($start_date->day > 1) && $end_date->day == $end_date->copy()->endOfMonth()->day){
                    if ($payment_partial_month < ($monthly_amount / 2)) {
                        if (!$second_payment_date_created) {
                            $second_payment_date_created = true;
                            continue;
                        }
                    }
                    $payment_schedules[] = [
                        'date' => $current_date->copy()->startOfMonth(),
                        'amount' => $monthly_amount_numeric,
                    ];
                }elseif ($start_date->day > 1 && $end_date->copy()->addDay()->day == $start_date->copy()->day) {
                    $payment_schedules[] = [
                        'date' => $current_date->copy()->startOfMonth()->addDays($start_date->day - 1),
                        'amount' => $monthly_amount_numeric,
                    ];

                    $current_date->addMonthNoOverflow();

                    if ($current_date->lte($end_date)) {
                        $payment_schedules[] = [
                            'date' => $current_date->copy()->startOfMonth()->addDays($start_date->day - 1),
                            'amount' => $monthly_amount_numeric,
                        ];
                    }
                }elseif ($start_date->day == 1 && $end_date->day != $end_date->copy()->endOfMonth()->day) {
                    $days_in_last_month = $end_date->day;
                    $proportion_of_last_month = $days_in_last_month / $end_date->copy()->endOfMonth()->day;
                    $last_payment_amount = $monthly_amount * $proportion_of_last_month;
                    if ($last_payment_amount < $monthly_amount) {
                        $last_payment_amount = $monthly_amount;
                    }
                    $last_payment_date = $current_date->copy()->startOfMonth()->subMonth();
                    if ($last_payment_date->lt($start_date->copy()->startOfMonth())) {
                        $last_payment_date = $start_date->copy()->startOfMonth()->subMonth();
                    }
                    $payment_schedules[] = [
                        'date' => $last_payment_date,
                        'amount' => number_format($last_payment_amount, 2),
                    ];
                    $current_date->addMonthNoOverflow();
                } else {
                    $payment_schedules[] = [
                        'date' => $current_date->copy()->startOfMonth(),
                        'amount' => $monthly_amount_numeric,
                    ];
                }

            }
        }
    }

    public function createQuarterlyPayments($applicant, $tenancy, $start_date, $end_date, &$payment_schedules)
    {
        $payment_schedules = [];
        $noOfApplicant = $tenancy->applicants()->count();
        $formatted_amount = $tenancy->total_rent / $noOfApplicant;
        $quarterly_amount_numeric = number_format(3 * $formatted_amount, 2);
        $quarterly_amount = (float) str_replace(',', '', $quarterly_amount_numeric);
        $first_payment_date = $start_date->copy()->subDays(7);
        $payment_schedules[] = [
            'date' => $first_payment_date->copy(),
            'amount' => $quarterly_amount,
        ];

        $current_date = $start_date->copy();
        $last_complete_quarter_added = false;
        $last_incomplete_index = null;
        while ($current_date->lte($end_date)) {
            $next_date = $current_date->copy()->addMonthsNoOverflow(3);
            if ($next_date->gt($end_date)) {
                $last_complete_quarter_end = $current_date->copy()->subMonthsNoOverflow(3)->endOfMonth();
                $last_complete_quarter_end_date = $last_complete_quarter_end->copy()->addMonthsNoOverflow(2)->endOfMonth();
                $last_complete_quarter_date = $last_complete_quarter_end->copy()->startOfMonth();

                if ($last_complete_quarter_date->lt($start_date)) {
                    $last_complete_quarter_date = $start_date->copy()->subMonthsNoOverflow(3)->startOfMonth();
                }

                $incomplete_days = $last_complete_quarter_end_date->diffInDays($end_date) + 1;
                $days_in_last_quarter = $end_date->copy()->subMonthsNoOverflow(3)->daysInMonth - 1;
                $incomplete_amount_numeric = number_format(($quarterly_amount / 3) * ($incomplete_days / $days_in_last_quarter), 2);
                $incomplete_amount = (float) str_replace(',', '', $incomplete_amount_numeric);
                foreach ($payment_schedules as $index => &$payment) {
                    if ($payment['date']->format('Y-m') === $last_complete_quarter_date->format('Y-m')) {
                        $payment['amount'] += $incomplete_amount;
                        $last_complete_quarter_added = true;
                        $last_incomplete_index = $index;
                        break;
                    }
                }
                unset($payment);
                if (!$last_complete_quarter_added) {
                    $payment_schedules[] = [
                        'date' => $last_complete_quarter_date,
                        'amount' => $incomplete_amount,
                    ];
                    $last_complete_quarter_added = true;
                }
                break;
            }
            $payment_schedules[] = [
                'date' => $next_date->copy(),
                'amount' => $quarterly_amount,
            ];

            $current_date = $next_date;
        }

        if ($last_incomplete_index !== null) {
            array_splice($payment_schedules, $last_incomplete_index + 1);
        }
    }

    public function createSinglePayment($applicant, $tenancy, $start_date, $end_date, &$payment_schedules)
    {
        $start = new \DateTime($start_date);
        $end = new \DateTime($end_date);
        $complete_months = $end->diff($start)->y * 12 + $end->diff($start)->m;
        $remaining_days = $end->diff($start)->d + 1;
        $noOfApplicant = $tenancy->applicants()->count();
        $monthly_amount = $tenancy->total_rent / $noOfApplicant;
        $complete_months_payment = $monthly_amount * $complete_months;
        $remaining_days_payment = ($monthly_amount / $end->format('t')) * $remaining_days;
        $total_payment_n = $complete_months_payment + $remaining_days_payment;
        $payment_schedule_date = $start_date->copy()->subDays(7);
        $total_payment_s = number_format($total_payment_n, 2);
        $total_payment = (float) str_replace(',', '', $total_payment_s);
        $payment_schedules[] = [
            'date' => $payment_schedule_date,
            'amount' => $total_payment
        ];
    }

    public function savePaymentSchedules($payment_schedules, $applicant, $tenancy)
    {
        $data = [];
        foreach ($payment_schedules as $payment_schedule) {
            $data[] = [
                'agency_id' => $applicant->agency_id,
                'applicant_id' => $applicant->id,
                'tenancy_id' => $tenancy->id,
                'date' => $payment_schedule['date'],
                'amount' => $payment_schedule['amount'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        PaymentSchedule::insert($data);
    }
}
