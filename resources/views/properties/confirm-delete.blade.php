<!DOCTYPE html>
<html>
<head>
    <title>Delete All Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">

    <h2 class="mb-3">Delete All Properties</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('properties.deleteAll') }}">
        @csrf
        <div class="mb-3">
            <label for="pin" class="form-label">Enter PIN</label>
            <input type="password" name="pin" id="pin" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger">Delete All</button>
    </form>

</body>
</html>
