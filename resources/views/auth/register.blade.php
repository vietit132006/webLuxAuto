<!DOCTYPE html>
<html>
<head>
    <title>Đăng ký</title>
</head>
<body>

<h2>Đăng ký tài khoản</h2>

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li style="color:red">{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="/register">
    @csrf

    <input type="text" name="name" placeholder="Tên"><br><br>

    <input type="email" name="email" placeholder="Email"><br><br>

    <input type="text" name="phone" placeholder="SĐT"><br><br>

    <input type="password" name="password" placeholder="Mật khẩu"><br><br>

    <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu"><br><br>

    <button type="submit">Đăng ký</button>
</form>

</body>
</html>
