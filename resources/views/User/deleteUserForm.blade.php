<!-- Include CSRF Token as needed -->
<form id="delete"
      action="{{route('admin.user.delete', ['id' => $user->id])}}"
      method="POST" style="display: none;">
    @csrf
    @method('DELETE')
    >
</form>

<a href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('delete').submit();" type="button" id="delete"
   class="mt-10 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-red-500">
    Delete
</a>
