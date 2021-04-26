<?php

namespace Tests\Unit\Models;

use App\Models\Borrower;
use App\Models\Community;
use App\Models\Bike;
use App\Models\Car;
use App\Models\Loanable;
use App\Models\Owner;
use App\Models\Trailer;
use App\Models\User;
use Tests\TestCase;

class LoanableTest extends TestCase
{
    public $borough;
    public $community;
    public $otherCommunity;

    public $memberOfBorough;
    public $memberOfCommunity;
    public $otherMemberOfCommunity;
    public $memberOfOtherCommunity;

    public $communityLoanable; // TODO
    public $boroughLoanable; // TODO

    public function setUp():void {
        parent::setUp();

        $this->borough = factory(Community::class)->create([
            'type' => 'borough',
        ]);
        $this->community = factory(Community::class)->create([
            'parent_id' => $this->borough->id,
        ]);
        $this->otherCommunity = factory(Community::class)->create([
            'parent_id' => $this->borough->id,
        ]);

        $this->memberOfBorough = factory(User::class)->create([
            'name' => 'memberOfBorough',
        ]);
        $this->borough->users()->attach($this->memberOfBorough, [
            'approved_at' => new \DateTime,
        ]);

        $this->memberOfCommunity = factory(User::class)->create([
            'name' => 'memberOfCommunity',
        ]);
        $this->community->users()->attach($this->memberOfCommunity, [
            'approved_at' => new \DateTime,
        ]);

        $this->otherMemberOfCommunity = factory(User::class)->create([
            'name' => 'otherMemberOfCommunity',
        ]);
        $this->community->users()->attach($this->otherMemberOfCommunity);

        $this->memberOfOtherCommunity = factory(User::class)->create([
            'name' => 'memberOfOtherCommunity',
        ]);
        $this->otherCommunity->users()->attach($this->memberOfOtherCommunity, [
            'approved_at' => new \DateTime,
        ]);

        foreach ([
            $this->memberOfBorough,
            $this->memberOfCommunity,
            $this->otherMemberOfCommunity,
            $this->memberOfOtherCommunity
        ] as $member) {
            $member->owner = new Owner;
            $member->owner->user()->associate($member);
            $member->owner->save();
        }

        foreach ([
            $this->memberOfCommunity,
            $this->otherMemberOfCommunity,
            $this->memberOfOtherCommunity
        ] as $member) {
            factory(Trailer::class)->create([
                'name' => "$member->name trailer",
                'owner_id' => $member->owner->id,
            ]);
        }
    }

    public function testLoanableNotAccessibleAccrossCommunitiesByDefault() {
        foreach ([
            $this->memberOfCommunity,
            $this->otherMemberOfCommunity,
            $this->memberOfOtherCommunity
        ] as $member) {
            $loanables = Loanable::accessibleBy($member)->pluck('name');
            $loanableIds = Loanable::accessibleBy($member)->pluck('id');
            $this->assertEquals(
              1,
              $loanables->count(),
              "too many loanables accessible to $member->name"
            );
            $this->assertEquals(
                $member->loanables()->first()->id,
                $loanableIds->first()
            );
        }
    }

    public function testLoanableBecomesAccessibleIfCommunityMembershipIsApproved() {
        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck('id');
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables->first()
        );

        $this->community->users()->updateExistingPivot($this->otherMemberOfCommunity->id, [
            'approved_at' => new \DateTime,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck('id')->sort();
        $this->assertEquals(2, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[1]
        );
    }

    public function testCarBecomesAccessibleIfBorrowerIsApproved() {
        $car = factory(Car::class)->create([
          'owner_id' => $this->memberOfCommunity->owner->id,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck('id');
        $this->assertEquals(2, $loanables->count());

        $loanables = Loanable::accessibleBy($this->otherMemberOfCommunity)->pluck('id');
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );

        $this->community->users()->updateExistingPivot($this->otherMemberOfCommunity->id, [
            'approved_at' => new \DateTime,
        ]);

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $loanables = Loanable::accessibleBy($this->otherMemberOfCommunity)
          ->orderBy('id')
          ->pluck('id');
        $this->assertEquals(2, $loanables->count());
        $firstId = $this->memberOfCommunity->loanables()->first()->id;
        $this->assertEquals(
            $firstId,
            $loanables[0],
            "$firstId not in " . implode(',', $loanables->toArray())
        );
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[1]
        );

        $borrower = new Borrower;
        $borrower->user()->associate($this->otherMemberOfCommunity);
        $borrower->approved_at = new \DateTime;
        $borrower->save();

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $loanables = Loanable::accessibleBy($this->otherMemberOfCommunity)
          ->orderBy('id')
          ->pluck('id');
        $this->assertEquals(3, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[1]
        );
        $this->assertEquals(
            $car->id,
            $loanables[2]
        );
    }

    public function testLoanableAccessibleThroughInheritedClasses() {
        factory(Car::class)->create([
            'owner_id' => $this->memberOfCommunity->owner->id,
        ]);

        $bikes = Bike::accessibleBy($this->memberOfCommunity)
            ->orderBy('id')
            ->pluck('id');
        $this->assertEquals(0, $bikes->count());

        $trailers = Trailer::accessibleBy($this->memberOfCommunity)
            ->orderBy('id')
            ->pluck('id');
        $this->assertEquals(1, $trailers->count());

        $cars = Car::accessibleBy($this->memberOfCommunity)
            ->orderBy('id')
            ->pluck('id');
        $this->assertEquals(1, $cars->count());

        $cars = Car::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy('id')
            ->pluck('id');
        $this->assertEquals(0, $cars->count());

        $this->community->users()->updateExistingPivot($this->otherMemberOfCommunity->id, [
            'approved_at' => new \DateTime,
        ]);

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $cars = Car::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy('id')
            ->pluck('id');
        $this->assertEquals(0, $cars->count());

        $borrower = new Borrower;
        $borrower->user()->associate($this->otherMemberOfCommunity);
        $borrower->approved_at = new \DateTime;
        $borrower->save();

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $cars = Car::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy('id')
            ->pluck('id');
        $this->assertEquals(1, $cars->count());
    }

    public function testLoanableAccessibleDownFromBorough() {
        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck('id');
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables->first()
        );

        $boroughLoanable = factory(Trailer::class)->create([
            'owner_id' => $this->memberOfBorough->owner->id,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->orderBy('id')->pluck('id');
        $this->assertEquals(2, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );
        $this->assertEquals(
            $this->memberOfBorough->loanables()->first()->id,
            $loanables[1]
        );
    }

    public function testLoanableIsAccessibleUpFromBoroughIfEnabled() {
        $boroughLoanable = factory(Trailer::class)->create([
            'owner_id' => $this->memberOfBorough->owner->id,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfBorough)->pluck('id');
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals($this->memberOfBorough->loanables[0]->id, $loanables[0]);

        $loanable = Trailer::find($this->memberOfCommunity->loanables[0]->id);
        $loanable->share_with_parent_communities = true;
        $loanable->save();

        $loanables = Loanable::accessibleBy($this->memberOfBorough)
            ->where('id', '!=', $this->memberOfBorough->loanables[0]->id)
            ->pluck('id');
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals($loanable->id, $loanables[0]);
    }

    public function testLoanableMethodGetCommunityForLoanBy() {
        $bike = factory(Bike::class)->create([
            'owner_id' => $this->memberOfCommunity->owner->id,
            'community_id' => $this->community->id,
        ]);

        // User is not approved on the loanable community
        // (you normally wouldn't be able to see the loanable to begin with)
        $community = $bike->getCommunityForLoanBy($this->otherMemberOfCommunity);
        $this->assertEquals(null, $community);

        // User is approved on the loanable community
        $this->community->users()->updateExistingPivot($this->otherMemberOfCommunity->id, [
            'approved_at' => new \DateTime,
        ]);

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $community = $bike->getCommunityForLoanBy($this->otherMemberOfCommunity);
        $this->assertEquals($this->community->id, $community->id);

        // User is approved on a child community of the loanable community
        $bike->community_id = $this->borough->id;
        $bike->save();
        $bike = $bike->fresh();
        $community = $bike->getCommunityForLoanBy($this->otherMemberOfCommunity);
        $this->assertEquals($this->community->id, $community->id);

        // User is approved on community of the loanable's owner
        $bike->community_id = null;
        $bike->save();
        $bike = $bike->fresh();

        $community = $bike->getCommunityForLoanBy($this->otherMemberOfCommunity);
        $this->assertEquals($this->community->id, $community->id);
    }
}
