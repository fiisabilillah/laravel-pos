<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto</title>
</head>
<body>
    <h2>Upload Foto</h2>

    <!-- Form untuk meng-upload gambar -->
    <form action="{{ route('image.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="image">Pilih Gambar:</label>
        <input type="file" name="image" id="image" required>
        <button type="submit">Upload</button>
    </form>

    <!-- Menampilkan gambar jika ada -->
    @isset($imagePath)
        <h3>Gambar yang di-upload:</h3>
        <img src="{{ $imagePath }}" alt="Uploaded Image" width="300px">
    @endisset

</body>
</html>
