<?php

namespace App\Models;

use Carbon\Carbon;

class Intention extends Action
{
    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            $loan = $model->loan;

            if ($model->executed_at) {
                return;
            }

            switch ($model->status) {
                case "completed":
                    if (!$model->loan->prePayment) {
                        $prePayment = new PrePayment();
                        $prePayment->loan()->associate($model->loan);
                        $prePayment->save();
                    }

                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
                case "canceled":
                    break;
                default:
                    // Auto complete intention if loanable is self service.
                    if (
                        $loan->loanable->is_self_service ||
                        !$loan->loanable->owner
                    ) {
                        $model->status = "completed";
                        $model->save();
                    } elseif (
                        $loan->loanable->owner->user->approvedCommunities
                            ->where("type", "private")
                            ->pluck("id")
                            ->intersect(
                                $loan->borrower->user->approvedCommunities
                                    ->where("type", "private")
                                    ->pluck("id")
                            )
                            ->intersect([$loan->community_id])
                            ->isNotEmpty()
                    ) {
                        $model->status = "completed";
                        $model->save();
                    }
                    break;
            }
        });
    }

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "intentions.*";
                }

                return $query->selectRaw("intentions.*");
            },
            "type" => function ($query = null) {
                if (!$query) {
                    return "'intention' AS type";
                }

                return $query->selectRaw("'intention' AS type");
            },
        ];
    }

    protected $fillable = ["message_for_borrower"];

    public $readOnly = false;

    public $items = ["loan"];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function complete($at = null)
    {
        $this->executed_at = new Carbon($at);
        $this->status = "completed";

        return $this;
    }

    public function isCompleted()
    {
        return $this->status == "completed";
    }

    public function cancel($at = null)
    {
        $this->executed_at = new Carbon($at);
        $this->status = "canceled";

        return $this;
    }

    public function isCanceled()
    {
        return $this->status == "canceled";
    }
}
