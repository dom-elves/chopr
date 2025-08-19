<div>
    <p>{{ $invite->user->name }} has invited you to join the group, <i>{{ $invite->group->name }}</i>, on chopr:</p>
    <p><i>{{ $invite->body }}</i></p>

    <a href="{{ route('invite.accept', ['token' => $invite->token]) }}">
        {{ $is_new_user ? 'Sign up' : 'Join' }}
    </a>
</div>