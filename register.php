<?php
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/core/csrf.php';
// Render register page UI only. Form posts to actions/auth_register.php
$msg = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'created') $msg = 'Account created successfully. You may now sign in.';
if (isset($_GET['error'])) $msg = 'There was an error creating your account.';
?>
<!-- Fullscreen fixed background -->
<div class="fixed inset-0" style="background-image: url('/eventsphere/assets/images/login_register_bg.png'); background-size: cover; background-position: center; background-attachment: fixed; z-index: 0;"></div>

<!-- Fullscreen dark overlay + backdrop blur -->
<div class="fixed inset-0 bg-black/40 backdrop-blur-md" style="z-index: 1;"></div>

<div class="min-h-screen flex items-center justify-center py-12">
  <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 z-10">
    <div class="text-center">
      <div class="text-indigo-600 font-montserrat text-2xl font-bold">EventSphere+</div>
      <h1 class="mt-4 text-2xl font-montserrat font-bold text-slate-800">Create an Account</h1>
      <p class="mt-2 text-sm text-slate-500">Start building your event presence — create an account below.</p>
    </div>
    <?php if ((getenv('APP_DEBUG') ?: ($_ENV['APP_DEBUG'] ?? null)) == '1'): ?>
      <div class="mt-2 text-center text-xs text-slate-400">
        <a href="/eventsphere/sso_google.php?debug=1">Debug: Show built Google OAuth URL</a>
      </div>
    <?php endif; ?>
    <?php if ($msg): ?>
      <div class="mt-4 p-2 rounded bg-emerald-50 text-emerald-700 text-sm"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
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
    <!-- Local Register Form -->
    <form method="post" action="/eventsphere/actions/auth_register.php" class="mt-6 space-y-4">
      <?php echo csrf_input_field(); ?>
      <div>
        <label class="block text-sm font-medium text-slate-700">Full name</label>
        <div class="mt-1 relative rounded-md shadow-sm">
          <input type="text" name="username" required class="block w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm font-opensans" placeholder="Jane Doe" />
          <div class="absolute inset-y-0 right-3 flex items-center"><i data-lucide="user" class="w-4 h-4 text-slate-400"></i></div>
        </div>
      </div>
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
          <input type="password" name="password" minlength="8" required class="block w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm font-opensans" placeholder="••••••••" />
          <div class="absolute inset-y-0 right-3 flex items-center"><i data-lucide="lock" class="w-4 h-4 text-slate-400"></i></div>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
        <div class="mt-1 relative rounded-md shadow-sm">
          <input type="password" name="password_confirm" minlength="8" required class="block w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm font-opensans" placeholder="••••••••" />
          <div class="absolute inset-y-0 right-3 flex items-center"><i data-lucide="check-circle" class="w-4 h-4 text-slate-400"></i></div>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <div></div>
        <div class="text-sm text-slate-500">Already have an account? <a href="/eventsphere/login.php" class="text-indigo-600 hover:underline">Log in</a></div>
      </div>
      <div>
        <button type="submit" class="w-full inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-white font-semibold hover:bg-indigo-700">Create Account</button>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
<?php
require_once __DIR__ . '/core/xml_handler.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_validate($csrf)) { $message = 'Invalid CSRF token.'; }
    else {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $email && $password) {
        $xh = new XmlHandler(__DIR__ . '/data/users.xml');
        $xml = $xh->read();
        if (!$xml) {
            $xml = new SimpleXMLElement("<users></users>");
        }
        // Compute a safe new ID by finding the max existing ID and adding 1
        $maxId = 0;
        foreach ($xml->user as $u) {
            $uid = (int)$u['id'];
            if ($uid > $maxId) $maxId = $uid;
        }
        $id = $maxId + 1;
        $user = $xml->addChild('user');
        $user->addAttribute('id', $id);
        $user->addChild('username', htmlspecialchars($username));
        $user->addChild('email', htmlspecialchars($email));
        $user->addChild('password', password_hash($password, PASSWORD_DEFAULT));
        $user->addChild('role', 'attendee');
        $xh->saveSimpleXML($xml);
        $message = 'User created. You may login now.';
    } else {
        $message = 'Please fill all fields.';
    }
    }
}
require_once __DIR__ . '/templates/header.php';
?>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
