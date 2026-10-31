// Tambahkan use ini bila belum ada:
use Illuminate\Support\Facades\Route;

// Tambahkan route ini di routes/web.php:
Route::view('/projects', 'projects.index')->name('projects.index');
