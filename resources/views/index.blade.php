<form id="login-form">
    <input type="email" id="email" placeholder="Email" required>
    <input type="password" id="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
<script>
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const res = await fetch('/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            })
        });

        const data = await res.json();
        if (data.access_token) {
            localStorage.setItem('token', data.access_token);
            localStorage.setItem('user_id', data.user.id);
            location.href = '/chat.html';
        } else {
            alert('Login failed');
        }
    });
</script>