<?php

namespace App\Presentation\Livewire\Branch;

use App\Domain\Branch\Actions\CreateUserAction;
use App\Domain\Branch\DTOs\UserData;
use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Управление сотрудниками')]
class UserManager extends Component
{
    public bool $showCreateForm = false;

    public bool $showEditForm = false;

    // Свойства формы
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public int $commission_percent = 0;

    public ?int $branch_id = null;

    public string $role_name = '';

    public string $successMessage = '';

    // Получаем список сотрудников (вместе с их филиалом и ролями для оптимизации N+1)
    #[Computed]
    public function users()
    {
        return User::with(['branch', 'roles'])->latest()->get();
    }

    // Для выпадающего списка филиалов
    #[Computed]
    public function branches()
    {
        return Branch::orderBy('name')->get();
    }

    // Для выпадающего списка ролей
    #[Computed]
    public function roles()
    {
        return Role::orderBy('name')->get();
    }

    // Маппинг ролей на русский язык
    public function getRoleNamesMap()
    {
        return [
            'Admin' => 'Администратор',
            'Branch Manager' => 'Управляющий',
            'Technician' => 'Инженер',
            'Storekeeper' => 'Кладовщик',
            'Accountant' => 'Бухгалтер',
        ];
    }

    // Получение русского названия роли
    public function getRoleNameInRussian($roleName)
    {
        return $this->getRoleNamesMap()[$roleName] ?? $roleName;
    }

    public function save(CreateUserAction $action)
    {
        // 1. Ищем пользователя по email, если мы в режиме редактирования
        $user = $this->showEditForm ? User::where('email', $this->email)->first() : null;

        // 2. Умная валидация
        $this->validate([
            'name' => 'required|string|max:255',
            // Игнорируем проверку уникальности email для текущего юзера
            'email' => 'required|email|unique:users,email'.($user ? ','.$user->id : ''),
            // Если это создание - пароль обязателен. Если редактирование - можно оставить пустым
            'password' => $this->showEditForm ? 'nullable|string|min:8' : 'required|string|min:8',
            'branch_id' => 'required|exists:branches,id',
            'role_name' => 'required|exists:roles,name',
            'commission_percent' => 'required|integer|min:0|max:100',
        ]);

        // 3. Режим ОБНОВЛЕНИЯ
        if ($this->showEditForm && $user) {
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'branch_id' => $this->branch_id,
                'commission_percent' => $this->commission_percent,
            ]);

            if (! empty($this->password)) {
                $user->update(['password' => Hash::make($this->password)]);
            }

            $user->syncRoles([$this->role_name]);
            $this->successMessage = 'Сотрудник успешно обновлен!';

        } else {
            // 4. Режим СОЗДАНИЯ
            $dto = new UserData(
                name: $this->name,
                email: $this->email,
                password: $this->password,
                branch_id: $this->branch_id,
                role_name: $this->role_name
            );

            $newUser = $action->execute($dto);
            $newUser->update(['commission_percent' => $this->commission_percent]);

            $this->successMessage = 'Сотрудник успешно добавлен!';
        }

        $this->resetForm();
        $this->showCreateForm = false;
        $this->showEditForm = false;
        unset($this->users);
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showCreateForm = true;
        $this->showEditForm = false;
    }

    public function edit($userId)
    {
        $this->resetForm();
        $user = User::with('roles')->findOrFail($userId);

        // Заполняем форму данными сотрудника
        $this->name = $user->name;
        $this->email = $user->email;
        $this->branch_id = $user->branch_id;
        $this->role_name = $user->roles->first()?->name ?? '';
        $this->commission_percent = $user->commission_percent;

        $this->showEditForm = true;
        $this->showCreateForm = false;
    }

    public function delete($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        $this->successMessage = 'Сотрудник удален!';
        unset($this->users);
    }

    public function closeModal()
    {
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function resetForm()
    {
        $this->reset(['name', 'email', 'password', 'branch_id', 'role_name']);
    }

    public function render()
    {
        return view('livewire.branch.user-manager');
    }
}
