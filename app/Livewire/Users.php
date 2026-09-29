<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class Users extends Component
{
    use WithPagination;

    public $name;
    public $username;
    public $user_type = 'staff';
    public $password;

    public $editId;
    public $staff_permissions = [];

    public function mount() {
        $roleStaff = Role::findByName('staff');
        $this->staff_permissions = $roleStaff->permissions()->pluck('id')->toArray();
    }

    public function create() {
        if (!auth()->user()->hasPermissionTo('users:create')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'user_type' => 'required|string|max:255|in:admin,staff',
            'password' => 'required|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        if ($user->user_type == 'admin') {
            $roleAdmin = Role::findByName('admin');
            $user->assignRole($roleAdmin);
        }else {
            $roleStaff = Role::findByName('staff');
            $user->assignRole($roleStaff);
        }
        
        $this->reset();
        session()->flash('success', __('User Added.'));
    }

    public function edit($id) {
        if (!auth()->user()->hasPermissionTo('users:update')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $u = User::findOrFail($id);
        $this->fill($u->toArray());
        $this->fill([
            'password' => ''
        ]);

        $this->editId = $id;
    }

    public function update() {
        if (!auth()->user()->hasPermissionTo('users:update')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->editId),
            ],
            'user_type' => 'required|string|max:255|in:admin,staff',
        ]);

        if (!empty($this->password)) {
            $this->validate([
                'password' => 'required|string|max:255',
            ]);
            $validate['password'] = Hash::make($validate['password']);
        }

        $u = User::findOrFail($this->editId);
        $u->update($validated);

        $this->reset();
        session()->flash('success', __('User updated.'));
    }

    public function delete($id) {
        if (!auth()->user()->hasPermissionTo('users:delete')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $u = User::findOrFail($id);

        if (User::where('user_type', 'admin')->count() < 2) {
            if ($u->user_type == 'admin') {
                session()->flash('failed', __('Unable to delete admin'));
                return;
            }
        }

        $u->roles()->detach(); 
        $u->permissions()->detach();

        $u->delete();
        session()->flash('success', __('User deleted.'));
    }

    public function updateStaffPermissions() {
        if (!auth()->user()->hasPermissionTo('users:update')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $role = Role::findByName('staff');
        $role->syncPermissions([]);

        $permissions = Permission::whereIn('id', $this->staff_permissions)->get();
        $role->syncPermissions($permissions);

        session()->flash('success', __('Staff permissions updated.'));
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('users:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.users', [
            'permissions' => Permission::get(),
            'users' => User::with(['roles.permissions', 'permissions'])->paginate(20)
        ]);
    }
}
