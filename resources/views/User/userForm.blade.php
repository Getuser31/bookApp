<form method="post" action="{{route('admin.user.create')}}">
    @csrf
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username:</label>
        <input
            id="username"
            name="name"
            type="text"
            placeholder="username"
            class="border border-gray-300 rounded-lg py-2 px-4 w-full max-w-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
    </div>
    <div class="mb-4 mt-2">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email:</label>
        <input
            id="email"
            name="email"
            type="email"
            placeholder="email"
            class="border border-gray-300 rounded-lg py-2 px-4 w-full max-w-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
    </div>
    <div class="mb-4 my-2">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password:</label>
        <input
            id="password"
            name="password"
            type="password"
            placeholder=""
            class="border border-gray-300 rounded-lg py-2 px-4 w-full max-w-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
    </div>
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="repeat-password">Repeat Password:</label>
        <input
            id="repeat-password"
            name="password_confirmation"
            type="password"
            placeholder=""
            class="border border-gray-300 rounded-lg py-2 px-4 w-full max-w-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
    </div>
    @if(session('admin'))
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="role">Role:</label>
        <select
            id="role"
            name="role_id"
            type="dropdown"
            class="border border-gray-300 rounded-lg py-2 px-4 w-full max-w-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            @foreach($roles as $role)
                <option value="{{$role->id}}">{{$role->name}}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="flex items-center justify-between">
        <button
            type="submit"
            class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            Submit
        </button>
    </div>
</form>
