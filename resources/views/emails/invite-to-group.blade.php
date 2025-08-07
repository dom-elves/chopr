<div>
    <p>{{ $invite->user->name }} has invited you to join the group, <i>{{ $invite->group->name }}</i>, on chopr:</p>
    <p><i>{{ $invite->body }}</i></p>
    @if ($is_new_user)
        <a href="{{ route('invite.signup', ['token' => $invite->token]) }}">
            {{ 'Sign up' }}
        </a>
    @else
        <a href="{{ route('invite.join', ['token' => $invite->token]) }}">
            {{ 'Join' }}
        </a>
    @endif
</div>