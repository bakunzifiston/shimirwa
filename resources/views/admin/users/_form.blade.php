<div class="grid gap-4 md:grid-cols-2 max-w-xl">
    <div>
        <label class="admin-label" for="name">Name</label>
        <input type="text" id="name" name="name" class="admin-input" value="{{ old('name', $user->name) }}" required>
    </div>
    <div>
        <label class="admin-label" for="email">Email</label>
        <input type="email" id="email" name="email" class="admin-input" value="{{ old('email', $user->email) }}" required>
    </div>
    <div>
        <label class="admin-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="admin-input" @if($user->exists) placeholder="Leave blank to keep current" @else required @endif>
    </div>
    <div>
        <label class="admin-label" for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="admin-input">
    </div>
</div>
