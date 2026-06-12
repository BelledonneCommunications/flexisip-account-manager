<?php

namespace App\Http\Controllers\Admin\Space;

use App\Group;
use App\Space;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\BlacklistedUsername;
use App\Rules\IsNotPhoneNumber;
use App\Rules\NoUppercase;
use App\Rules\SIPUsername;

class GroupController extends Controller
{
    public function index(Request $request, Space $space)
    {
        $request->validate([
            'order_by' => 'in:name,username,strategy,accounts_count,updated_at',
            'order_sort' => 'in:asc,desc',
        ]);

        $group = $space->groups()->orderBy($request->input('order_by', 'updated_at'), $request->input('order_sort', 'desc'));

        return view('admin.space.group.index', [
            'space' => $space,
            'groups' => $group->paginate(20),
        ]);
    }

    public function create(Space $space)
    {
        return view('admin.space.group.create_edit', [
            'space' => $space,
            'group' => new Group,
        ]);
    }

    public function store(Request $request, Space $space)
    {
        $request->validate([
            'name' => ['required', 'unique:groups'],
            'username' => [
                'required',
                'unique:groups',
                'unique:accounts',
                new NoUppercase,
                new IsNotPhoneNumber,
                new BlacklistedUsername,
                new SIPUsername
            ],
            'strategy' => ['string', Rule::in(array_keys(Group::GROUP_STRATEGIES))],
        ]);

        $group = new Group;
        $group->name = $request->name;
        $group->username = $request->username;
        $group->strategy = $request->strategy;
        $group->space_id = $space->id;
        $group->save();

        return redirect()->route('admin.spaces.groups.edit', [$space, $group]);
    }

    public function edit(Request $request, Space $space, Group $group)
    {
        $query = $group->accounts();

        if ($request->has('search')) {
            $query->where('username', 'like', '%' . $request->input('search') . '%')
                ->where('domain', $space->domain);
        }

        return view('admin.space.group.create_edit', [
            'space' => $space,
            'group' => $group,
            'accounts' => $query->paginate(20),
        ]);
    }

    public function update(Request $request, Space $space, Group $group)
    {
        $request->validate([
            'name' => ['required', Rule::unique('groups')->ignore($group->id)],
            'strategy' => ['string', Rule::in(array_keys(Group::GROUP_STRATEGIES))],
        ]);

        $group->name = $request->name;
        $group->strategy = $request->strategy;
        $group->save();

        return redirect()->route('admin.spaces.groups.index', $space);
    }

    public function delete(Space $space, Group $group)
    {
        return view('admin.space.group.delete', [
            'space' => $space,
            'group' => $group,
        ]);
    }

    public function destroy(Space $space, Group $group)
    {
        $group->delete();
        return redirect()->route('admin.spaces.groups.index', $space);
    }

    public function add(Request $request, Space $space, Group $group)
    {
        $query = $space->accounts();

        if ($request->has('search')) {
            $query->where('username', 'like', '%' . $request->input('search') . '%')
                ->where('domain', $space->domain);
        }

        if ($request->has('order_by')) {
            $query->orderBy(
                $request->input('order_by'),
                $request->input('order_sort', 'asc')
            );
        }

        return view('admin.space.group.add', [
            'space' => $space,
            'group' => $group,
            'accounts' => $query->paginate(20),
        ]);
    }

    public function attach(Request $request, Space $space, Group $group)
    {
        $request->validate([
            'account_ids' => 'required|array',
            'account_ids.*' => 'exists:accounts,id',
        ]);

        $group->accounts()->syncWithoutDetaching($request->input('account_ids'));
        return redirect()->route('admin.spaces.groups.edit', [$space, $group]);
    }

    public function detach(Request $request, Space $space, Group $group)
    {
        $request->validate([
            'account_ids' => 'required|array',
            'account_ids.*' => 'required|exists:accounts,id',
        ]);

        $group->accounts()->detach($request->input('account_ids'));
        return redirect()->route('admin.spaces.groups.edit', [$space, $group]);
    }
}
