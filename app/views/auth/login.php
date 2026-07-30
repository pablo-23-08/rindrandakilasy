<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>connexion</title>
  <link href="/rindrandakilasy/public/assets/css/output.css" rel="stylesheet">
  <link rel="icon" href="assets/img/google-icons/chess_rook.svg" type="image/x-icon">
</head>

<body class="bg-gray-50 h-screen flex items-center justify-center">

  <div class="bg-white border rounded p-8 w-full max-w-md">
    
    <!-- Header with Logo -->
    <div class="flex items-center justify-center gap-4 mb-8">
      <img src="assets/img/google-icons/chess_rook.svg" alt="Logo" width="100" height="100" class="border">
      <h1 class="text-2xl font-bold">RindranDakilasy</h1>
    </div>

    <!-- Message d'erreur -->
    <?php if (!empty($_SESSION['error'])): ?>
      <p class="text-red-500 text-sm text-center mb-4"><?= $_SESSION['error'] ?></p>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="/rindrandakilasy/public/login" class="space-y-6">
      
      <div>
        <label class="block text-sm font-medium mb-2">E-mail</label>
        <input type="email" name="email" class="w-full border rounded p-2">
      </div>

      <div>
        <label class="block text-sm font-medium mb-2">Mot de passe</label>
        <input type="password" name="password" class="w-full border rounded p-2">
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
        Se connecter
      </button>

    </form>

  </div>

</body>
</html>