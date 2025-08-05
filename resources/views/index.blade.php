<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reset default styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            background: #f0f4f8;
        }

        form#login-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        form input {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s;
        }

        form input:focus {
            border-color: #007BFF;
            outline: none;
        }

        form button {
            padding: 0.75rem 1rem;
            background: #007BFF;
            color: white;
            border: none;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }

        form button:hover {
            background: #0056b3;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <form id="login-form">
        <h2 style="text-align:center;">Login</h2>
        <input type="email" id="email" placeholder="Email" required>
        <input type="password" id="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <div class="error" id="error-msg"></div>
    </form>

    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMsg = document.getElementById('error-msg');

            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                const data = await res.json();

                if (res.ok && data.access_token) {
                    localStorage.setItem('token', data.access_token);
                    localStorage.setItem('user_id', data.user.id);
                    location.href = '/chat';
                } else {
                    errorMsg.textContent = data.message || 'Login failed';
                }
            } catch (error) {
                errorMsg.textContent = 'Something went wrong. Try again.';
            }
        });
    </script>
</body>
</html>
