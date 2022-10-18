<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;
use App\Repositories\LoanRepository;

class ExtensionRequest extends BaseRequest
{
    private $loanMemo;

    /*
       Request is authorized for
         - admins
         - owner of the loanable
         - borrower involved in the current loan
    */
    public function authorize()
    {
        $loan = $this->fetchLoan();

        $user = $this->user();

        if ($user->isAdmin()) {
            return true;
        }

        if (
            $loan->loanable->owner &&
            $user->owner &&
            $user->owner->id === $loan->loanable->owner->id
        ) {
            return true;
        }

        if ($user->borrower->id === $loan->borrower->id) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        $loan = $this->fetchLoan();

        $min =
            max(
                $loan->duration_in_minutes,
                $loan
                    ->extensions()
                    ->where("status", "=", "completed")
                    ->max("new_duration")
            ) + 15;

        return [
            "new_duration" => ["min:$min", "numeric"],
        ];
    }

    private function fetchLoan()
    {
        if ($this->loanMemo) {
            return $this->loanMemo;
        }

        $loanRepository = new LoanRepository(new Loan());
        $this->loanMemo = $loanRepository->find(
            $this->redirectAuth(BaseRequest::class),
            $this->route("loan_id")
        );

        return $this->loanMemo;
    }
}
