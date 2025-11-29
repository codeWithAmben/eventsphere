<?php
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/core/csrf.php';
$message = '';
if (isset($_GET['error'])) $message = 'Invalid credentials. Please try again.';
?>


<!-- Fullscreen fixed background -->
<div class="fixed inset-0" style="background-image: url('/eventsphere/assets/images/login_register_bg.png'); background-size: cover; background-position: center; background-attachment: fixed; z-index: 0;"></div>

<!-- Fullscreen dark overlay + backdrop blur -->
<div class="fixed inset-0 bg-black/40 backdrop-blur-md" style="z-index: 1;"></div>

<div class="min-h-screen flex items-center justify-center py-12">
  <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 z-10">
    <div class="text-center">
      <div class="text-indigo-600 font-montserrat text-2xl font-bold">EventSphere+</div>
      <h1 class="mt-4 text-2xl font-montserrat font-bold text-slate-800">Welcome Back</h1>
      <p class="mt-2 text-sm text-slate-500">Sign in to access your dashboard.</p>
    </div>
    <?php if ((getenv('APP_DEBUG') ?: ($_ENV['APP_DEBUG'] ?? null)) == '1'): ?>
      <div class="mt-2 text-center text-xs text-slate-400">
        <a href="/eventsphere/sso_google.php?debug=1">Debug: Show built Google OAuth URL</a>
      </div>
    <?php endif; ?>
    <?php if ($message): ?><div class="mt-4 p-2 rounded bg-rose-50 text-rose-700 text-sm"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <!-- SSO Buttons -->
    <div class="mt-6 space-y-3">
      <a href="/eventsphere/sso_google.php" class="w-full inline-flex items-center gap-3 justify-center border border-slate-200 px-4 py-2 rounded bg-white text-slate-700 hover:bg-slate-50">
        <i data-lucide="globe" class="w-4 h-4"></i>
        Continue with Google
      </a>
      <a href="#" class="w-full inline-flex items-center gap-3 justify-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        <i data-lucide="users" class="w-4 h-4"></i>
        Continue with Facebook
      </a>
    </div>
    <div class="mt-6 flex items-center gap-3">
      <div class="flex-1 border-t border-slate-200"></div>
      <div class="text-sm text-slate-500">Or continue with email</div>
      <div class="flex-1 border-t border-slate-200"></div>
    </div>
    <form method="post" action="/eventsphere/actions/auth_login.php" class="mt-6 space-y-4">
      <?php echo csrf_input_field(); ?>
      <div>
        <label class="block text-sm font-medium text-slate-700">Email</label>
        <div class="mt-1 relative rounded-md shadow-sm">
          <input type="email" name="email" required class="block w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm font-opensans" placeholder="you@example.com" />
          <div class="absolute inset-y-0 right-3 flex items-center"><i data-lucide="mail" class="w-4 h-4 text-slate-400"></i></div>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Password</label>
        <div class="mt-1 relative rounded-md shadow-sm">
          <input type="password" name="password" required class="block w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm font-opensans" placeholder="••••••••" />
          <div class="absolute inset-y-0 right-3 flex items-center"><i data-lucide="lock" class="w-4 h-4 text-slate-400"></i></div>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="remember" class="form-checkbox h-4 w-4 text-indigo-600" />
          <span class="text-sm text-slate-600 font-opensans">Remember me</span>
        </label>
        <a href="/eventsphere/forgot_password.php" class="text-sm text-indigo-600 hover:underline">Forgot Password?</a>
      </div>
      <div>
        <button type="submit" class="w-full inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-white font-semibold hover:bg-indigo-700">Sign In</button>
      </div>
    </form>
    <div class="mt-6 text-center text-sm text-slate-500">Don't have an account? <a href="/eventsphere/register.php" class="text-indigo-600 hover:underline">Sign up</a></div>
  </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
