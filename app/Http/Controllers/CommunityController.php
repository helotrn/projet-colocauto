<?php

namespace App\Http\Controllers;

use App\Events\RegistrationRejectedEvent;
use App\Exports\CommunitiesExport;
use App\Http\Controllers\CommunityController;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Community\CreateRequest;
use App\Http\Requests\Community\DestroyRequest;
use App\Http\Requests\Community\UpdateRequest;
use App\Http\Requests\Community\CommunityUserTagRequest;
use App\Models\Community;
use App\Models\User;
use App\Models\Pricing;
use App\Repositories\CommunityRepository;
use App\Repositories\UserRepository;
use Excel;
use Illuminate\Validation\ValidationException;

class CommunityController extends RestController
{
    public function __construct(
        CommunityRepository $repository,
        Community $model,
        TagController $tagController,
        UserController $userController,
        UserRepository $UserRepository
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->tagController = $tagController;
        $this->userController = $userController;
        $this->userRepo = $UserRepository;
    }

    public function index(Request $request)
    {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        switch ($request->headers->get("accept")) {
            case "text/csv":
                $filename = $this->respondWithCsv(
                    $request,
                    $items,
                    $this->model
                );
                return response(
                    env("BACKEND_URL_FROM_BROWSER") . $filename,
                    201
                );
            default:
                return $this->respondWithCollection($request, $items, $total);
        }
    }

    public function create(CreateRequest $request)
    {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        if(sizeof($item->pricings) === 0){
            // create a default simple pricing
            $pricing = new Pricing();
            $pricing->rule = '$OBJET.cost_per_km * $KM';
            $pricing->name = 'Par défaut';
            $pricing->object_type = null;
            $pricing->community_id = $item->id;
            $pricing->save();
            $item->pricings[] = $pricing;
        }
        return $this->respondWithItem($request, $item, 201);
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function retrieve(Request $request, $id)
    {
        $item = $this->repo->find($request, $id);

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function destroy(DestroyRequest $request, $id)
    {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function indexCommunityUserTags(
        Request $request,
        $communityId,
        $userId
    ) {
        $community = $this->repo->find($request, $communityId);
        User::accessibleBy($request->user())->findOrFail($userId);
        $user = $community->users->find($userId);

        $request->merge(["community_users.id" => $user->pivot->id]);

        return $this->tagController->index($request);
    }

    public function updateCommunityUserTags(
        CommunityUserTagRequest $request,
        $communityId,
        $userId,
        $tagId
    ) {
        $community = $this->repo->find($request, $communityId);
        User::accessibleBy($request->user())->findOrFail($userId);
        $user = $community->users->find($userId);

        if (!$user) {
            return abort(404);
        }

        if ($tag = $user->pivot->tags()->find($tagId)) {
            return $this->respondWithItem($request, $tag);
        }

        $user->pivot->tags()->attach($tagId);

        return $this->respondWithItem(
            $request,
            $user->pivot->tags()->find($tagId)
        );
    }

    public function destroyCommunityUserTags(
        CommunityUserTagRequest $request,
        $communityId,
        $userId,
        $tagId
    ) {
        $community = $this->repo->find($request, $communityId);
        User::accessibleBy($request->user())->findOrFail($userId);
        $user = $community->users->find($userId);

        if (!$user) {
            return abort(404);
        }

        if ($tag = $user->pivot->tags()->find($tagId)) {
            $user->pivot->tags()->detach($tagId);
            return $this->respondWithItem($request, $tag);
        }

        return abort(404);
    }

    public function indexUsers(Request $request, $id)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $id
        );

        $items = $community->users;

        switch ($request->headers->get("accept")) {
            case "text/csv":
                $filename = $this->respondWithCsv($request, $items, new User());
                return response(
                    env("BACKEND_URL_FROM_BROWSER") . $filename,
                    201
                );
            default:
                return $this->respondWithCollection($request, $items, $total);
        }
    }

    public function retrieveUsers(Request $request, $id, $userId)
    {
        $community = $this->repo->find($request, $id);

        $request->merge(["communities.id" => $id]);

        return $this->userController->retrieve($request, $userId);
    }

    public function listAdmins(Request $request, $id)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $id
        );

        $items = $community->admins;

        return $this->respondWithCollection($request, $items, sizeof($items));
    }

    public function createUsers(Request $request, $id)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $id
        );

        $userId = $request->get("id");
        $user = $this->userRepo->find(
            $request->redirectAuth(Request::class),
            $userId
        );

        if ($community->users->where("id", $userId)->isEmpty()) {
            $community->users()->attach($userId);

            return $this->respondWithItem($request, $user);
        }

        return $this->respondWithItem(
            $request,
            $community->users->where("id", $userId)->first()
        );
    }

    public function createAdmins(Request $request, $id)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $id
        );

        $userId = $request->get("id");
        $user = $this->userRepo->find(
            $request->redirectAuth(Request::class),
            $userId
        );

        if ($community->admins->where("id", $userId)->isEmpty()) {
            $community->admins()->attach($userId);

            return $this->respondWithItem($request, $user);
        }

        return $this->respondWithItem(
            $request,
            $community->admins->where("id", $userId)->first()
        );
    }

    public function updateUsers(Request $request, $id, $userId)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $id
        );

        $user = $this->userRepo->find(
            $request->redirectAuth(Request::class),
            $userId
        );

        if ($community->users->where("id", $userId)->isEmpty()) {
            return $this->respondWithMessage(null, 404);
        }

        $this->userRepo->update($request, $userId, $request->json()->all());

        return $this->respondWithItem(
            $request,
            $community
                ->users()
                ->where("users.id", $userId)
                ->first()
        );
    }

    public function destroyUsers(Request $request, $communityId, $userId)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $communityId
        );
        $user = $this->userRepo->find(
            $request->redirectAuth(Request::class),
            $userId
        );

        if ($community->users->where("id", $userId)->isEmpty()) {
            return $this->respondWithMessage(null, 404);
        }

        $wasApproved = !!$community->users()->find($userId)->pivot->approved_at;
        $community->users()->detach($user);

        if (!$wasApproved) {
            event(new RegistrationRejectedEvent($user, $community));
        }

        return $this->respondWithItem($request, $user);
    }

    public function destroyAdmins(Request $request, $communityId, $userId)
    {
        $community = $this->repo->find(
            $request->redirectAuth(Request::class),
            $communityId
        );
        $user = $this->userRepo->find(
            $request->redirectAuth(Request::class),
            $userId
        );

        if ($community->admins->where("id", $userId)->isEmpty()) {
            return $this->respondWithMessage(null, 404);
        }

        $community->admins()->detach($user);

        return $this->respondWithItem($request, $user);
    }

    public function getExpensesBalance(Request $request, $communityId)
    {
        $community = $this->repo->find($request, $communityId);

        // for each user ...
        $users = $community->users->map(function ($user) use ($communityId) {
            // compute expenses
            $user->balance = 0;
            $user->balance = $user->expenses->where('type', 'credit')->sum('amount');
            $user->balance -= $user->expenses->where('type', 'debit')->sum('amount');
            // compute refunds
            $user->balance += $user->debitedRefunds->sum('amount');
            $user->balance -= $user->creditedRefunds->sum('amount');

            if( $user->owner ){
                // remove owned loanable costs
                $user->balance = $user->owner->loanables->reduce(function ($carry, $loanable) use ($communityId) {
                    if( in_array($communityId, $loanable->getCommunityIdsAttribute()) ) {
                        $carry -= $loanable->expenses->where('type', 'credit')->sum('amount');
                        $carry +=  $loanable->expenses->where('type', 'debit')->sum('amount');
                    }
                    return $carry;
                }, $user->balance);
            }
            return (object) [
                "id" => $user->id,
                "balance" => $user->balance,
                "full_name" => $user->full_name,
            ];
        });

        // make all balance=0 users appear grouped at the end
        $users = $users->sort(function($u1, $u2) {
            if($u1->balance == 0) {
                return $u2->balance == 0 ? 0 : 1;
            } else {
                return $u2->balance == 0 ? -1 : $u1->id - $u2->id;
            }
        })->values();

        // propose refund transactions to reach perfect balance
        $refunds = [];
        $debtors = $users->filter(function($user){
          return $user->balance < 0;
        })->sortBy('balance');
        $creditors = $users->filter(function($user){
          return $user->balance > 0;
        })->sortByDesc('balance');

        if( $debtors->isNotEmpty() ){
          // [1] search matching balances to wipe out
          foreach($debtors->all() as $debtor) {
            $toWipe = $creditors->filter(function($creditor) use ($debtor) {
              return $debtor->balance == -$creditor->balance;
            });
            if(!$toWipe->isEmpty()) {
              $refunds[] = [
                "user_id" => $debtor->id,
                "user_full_name" => $debtor->full_name,
                "credited_user_id" => $toWipe->first()->id,
                "credited_user_full_name" => $toWipe->first()->full_name,
                "amount" => -$debtor->balance,
              ];
              $debtors = $debtors->reject(function($user) use ($debtor){
                return $debtor->id == $user->id;
              });
              $creditors = $creditors->reject(function($user) use ($toWipe){
                return $toWipe->first()->id == $user->id;
              });
            }
          }
        }

        while( $debtors->isNotEmpty() && $creditors->isNotEmpty() ){
          // [2] fill the bigger gap
          $debtor = $debtors->first();
          $creditor = $creditors->first();
          $refunds[] = [
            "user_id" => $debtor->id,
            "user_full_name" => $debtor->full_name,
            "credited_user_id" => $creditor->id,
            "credited_user_full_name" => $creditor->full_name,
            "amount" => $debtor->balance + $creditor->balance > 0 ? -$debtor->balance : $creditor->balance,
          ];

          // if debtor debt is not enougth to refund creditor ...
          if( $debtor->balance + $creditor->balance > 0 ){
            // ... decrease creditor balance ...
            $creditors = $creditors->map(function($user) use($creditor, $debtor) {
              if( $user->id == $creditor->id ) {
                return (object) [
                  "id" => $user->id,
                  "full_name" => $user->full_name,
                  "balance" => $user->balance + $debtor->balance,
                ];
              }
              return $user;
            })->sortByDesc('balance');

            // ... and remove the debtor
            $debtors = $debtors->reject(function($user) use ($debtor){
              return $debtor->id == $user->id;
            });

          } else {

            // if debtor debt is too much to refund the creditor ...
            if( $debtor->balance + $creditor->balance < 0 ) {
              /// ... decrease the debtor debt
              $debtors = $debtors->map(function($user) use($creditor, $debtor) {
                if( $user->id == $debtor->id ) {
                  return (object) [
                    "id" => $user->id,
                    "full_name" => $user->full_name,
                    "balance" => $user->balance + $creditor->balance,
                  ];
                }
                return $user;
              })->sortBy('balance');
            } else {
              // else remove the debtor
              $debtors = $debtors->reject(function($user) use ($debtor){
                return $debtor->id == $user->id;
              });
            }

            // and remove the creditor
            $creditors = $creditors->reject(function($user) use ($creditor){
              return $creditor->id == $user->id;
            });

          }
        }

        // add loanables without owner to the balance
        $withoutOwner = $community->loanables
            ->filter(function ($loanable) {
                return $loanable->owner == null;
            })
            ->map(function ($loanable) use ($users) {
                $balance = $loanable->expenses->where('type', 'debit')->sum('amount');
                $balance -= $loanable->expenses->where('type', 'credit')->sum('amount');
                $users->prepend((object) [
                    "loanable_id" => $loanable->id,
                    "balance" => $balance,
                    "full_name" => $loanable->name,
                ]);
            });

        return response([
          "users" => $users->all(),
          "refunds" => $refunds,
        ], 200);
    }

    public function template(Request $request)
    {
        $template = [
            "item" => [
                "name" => "",
                "chat_group_url" => "",
                "description" => "",
                "long_description" => "",
                "type" => "neighborhood",
                "pricings" => [],
            ],
            "filters" => $this->model::$filterTypes,
            "form" => [
                "name" => [
                    "type" => "text",
                ],
                "description" => [
                    "type" => "textarea",
                ],
                "long_description" => [
                    "type" => "html",
                ],
                "chat_group_url" => [
                    "type" => "text",
                ],
                "type" => [
                    "type" => "select",
                    "label" => "Type",
                    "options" => [
                        [
                            "text" => "Privée",
                            "value" => "private",
                        ],
                        [
                            "text" => "Voisinage",
                            "value" => "neighborhood",
                        ],
                        [
                            "text" => "Quartier",
                            "value" => "borough",
                        ],
                    ],
                ],
                "parent_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "communities",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name",
                            "type" => "borough",
                        ],
                    ],
                ],
            ],
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        return $template;
    }
}
