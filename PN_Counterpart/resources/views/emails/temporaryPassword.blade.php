<p>Hello,</p>
<p>You have been added to the system. Here are your login details:</p>
<p><strong>Login ID:</strong> {{ $loginId }}</p>
<p><strong>Temporary Password:</strong> {{ $tempPassword }}</p>
<p>Please log in and change your password immediately.</p>
<p>
    <a href="{{ route('login') }}" style="
        display: inline-block;
        padding: 10px 20px;
        background-color: #007BFF;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    ">Log In</a>
</p>