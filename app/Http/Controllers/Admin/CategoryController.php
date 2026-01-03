<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->hasPermission('view_categories') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к управлению категориями');
            }
            return $next($request);
        });
    }

    /**
     * Отображает список категорий
     */
    public function index()
    {
        $categories = Category::orderBy('parent_id')->orderBy('name')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Показывает форму создания категории
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.categories.create', compact('categories'));
    }

    /**
     * Сохраняет новую категорию
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);

        Category::create($request->only(['name', 'parent_id', 'description', 'order']));

        return redirect()->route('admin.categories.index')->with('success', 'Категория успешно создана');
    }

    /**
     * Показывает форму редактирования категории
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $categories = Category::where('id', '!=', $id)->orderBy('name')->get();
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    /**
     * Обновляет информацию о категории
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);

        $category->update($request->only(['name', 'parent_id', 'description', 'order']));

        return redirect()->route('admin.categories.index')->with('success', 'Категория успешно обновлена');
    }

    /**
     * Удаляет категорию
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Проверяем, есть ли у категории дочерние элементы
        if ($category->children()->count() > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'Невозможно удалить категорию с дочерними элементами');
        }
        
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Категория успешно удалена');
    }
}