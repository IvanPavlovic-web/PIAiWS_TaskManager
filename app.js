const API = '/api';

let categories = [];
let activeCategoryId = null;

// ---------- helpers ----------

async function apiCall(path, options = {}) {
    const res = await fetch(API + path, {
        headers: { 'Content-Type': 'application/json' },
        ...options,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        throw new Error(data.error || 'Greška na serveru.');
    }
    return data;
}

function show(id) {
    document.getElementById(id).style.display = '';
}
function hide(id) {
    document.getElementById(id).style.display = 'none';
}

const statusLabels = {
    pending: 'Na čekanju',
    in_progress: 'U toku',
    completed: 'Završeno',
};

// ---------- view switching ----------

function showAuthView(view) {
    hide('dashboardView');
    hide('topbar');
    if (view === 'login') {
        show('loginView');
        hide('registerView');
    } else {
        show('registerView');
        hide('loginView');
    }
}

function showDashboard(username) {
    hide('loginView');
    hide('registerView');
    show('dashboardView');
    show('topbar');
    document.getElementById('usernameDisplay').textContent = username;
    loadCategories();
    loadTasks();
}

// ---------- auth ----------

document.getElementById('showRegister').addEventListener('click', (e) => {
    e.preventDefault();
    showAuthView('register');
});

document.getElementById('showLogin').addEventListener('click', (e) => {
    e.preventDefault();
    showAuthView('login');
});

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value;
    const errorEl = document.getElementById('loginError');
    errorEl.textContent = '';

    try {
        const user = await apiCall('/login', {
            method: 'POST',
            body: JSON.stringify({ username, password }),
        });
        showDashboard(user.username);
    } catch (err) {
        errorEl.textContent = err.message;
    }
});

document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('registerUsername').value.trim();
    const password = document.getElementById('registerPassword').value;
    const errorEl = document.getElementById('registerError');
    errorEl.textContent = '';

    try {
        const user = await apiCall('/register', {
            method: 'POST',
            body: JSON.stringify({ username, password }),
        });
        showDashboard(user.username);
    } catch (err) {
        errorEl.textContent = err.message;
    }
});

document.getElementById('logoutBtn').addEventListener('click', async () => {
    await apiCall('/logout');
    activeCategoryId = null;
    showAuthView('login');
});

// ---------- categories ----------

async function loadCategories() {
    const res = await apiCall('/categories');
    categories = res.data;
    renderCategoryList();
    renderCategorySelect();
}

function renderCategoryList() {
    const list = document.getElementById('categoryList');
    list.innerHTML = '';

    categories.forEach((cat) => {
        const li = document.createElement('li');
        li.className = cat.id === activeCategoryId ? 'active' : '';

        const label = document.createElement('span');
        label.textContent = cat.name;
        label.style.flex = '1';
        label.addEventListener('click', () => {
            activeCategoryId = cat.id;
            renderCategoryList();
            loadTasks();
        });

        const actions = document.createElement('span');
        actions.className = 'category-actions';

        const editBtn = document.createElement('button');
        editBtn.textContent = '✎';
        editBtn.title = 'Izmijeni';
        editBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            const newName = prompt('Novi naziv kategorije:', cat.name);
            if (newName && newName.trim() !== '') {
                await apiCall(`/categories/${cat.id}`, {
                    method: 'PUT',
                    body: JSON.stringify({ name: newName.trim() }),
                });
                loadCategories();
            }
        });

        const delBtn = document.createElement('button');
        delBtn.textContent = '🗑';
        delBtn.title = 'Obriši';
        delBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            if (confirm(`Obrisati kategoriju "${cat.name}"?`)) {
                await apiCall(`/categories/${cat.id}`, { method: 'DELETE' });
                if (activeCategoryId === cat.id) activeCategoryId = null;
                loadCategories();
                loadTasks();
            }
        });

        actions.appendChild(editBtn);
        actions.appendChild(delBtn);
        li.appendChild(label);
        li.appendChild(actions);
        list.appendChild(li);
    });
}

function renderCategorySelect() {
    const select = document.getElementById('taskCategory');
    const current = select.value;
    select.innerHTML = '<option value="">Bez kategorije</option>';
    categories.forEach((cat) => {
        const opt = document.createElement('option');
        opt.value = cat.id;
        opt.textContent = cat.name;
        select.appendChild(opt);
    });
    select.value = current;
}

document.getElementById('categoryForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('categoryName');
    const name = input.value.trim();
    if (!name) return;

    await apiCall('/categories', {
        method: 'POST',
        body: JSON.stringify({ name }),
    });
    input.value = '';
    loadCategories();
});

document.getElementById('clearCategoryFilter').addEventListener('click', () => {
    activeCategoryId = null;
    renderCategoryList();
    loadTasks();
});

// ---------- tasks ----------

function categoryName(id) {
    const cat = categories.find((c) => c.id === Number(id));
    return cat ? cat.name : null;
}

async function loadTasks() {
    const params = new URLSearchParams();
    if (activeCategoryId) params.set('category', activeCategoryId);

    const status = document.getElementById('statusFilter').value;
    if (status) params.set('status', status);

    const search = document.getElementById('searchInput').value.trim();
    if (search) params.set('search', search);

    const res = await apiCall('/tasks?' + params.toString());
    renderTaskList(res.data);
}

function renderTaskList(tasks) {
    const container = document.getElementById('taskList');
    container.innerHTML = '';

    if (tasks.length === 0) {
        container.innerHTML = '<div class="empty-state">Nema zadataka koji odgovaraju kriterijumima.</div>';
        return;
    }

    tasks.forEach((task) => {
        const card = document.createElement('div');
        card.className = 'task-card' + (task.status === 'completed' ? ' completed' : '');

        const main = document.createElement('div');
        main.className = 'task-card-main';

        const title = document.createElement('div');
        title.className = 'task-card-title';
        title.textContent = task.title;

        const desc = document.createElement('div');
        desc.className = 'task-card-desc';
        desc.textContent = task.description || '';

        const meta = document.createElement('div');
        meta.className = 'task-card-meta';

        const badge = document.createElement('span');
        badge.className = 'badge badge-' + task.status;
        badge.textContent = statusLabels[task.status] || task.status;
        meta.appendChild(badge);

        const priority = document.createElement('span');
        priority.textContent = 'Prioritet: ' + task.priority;
        meta.appendChild(priority);

        if (task.due_date) {
            const due = document.createElement('span');
            due.textContent = 'Rok: ' + task.due_date;
            meta.appendChild(due);
        }

        const catName = categoryName(task.category_id);
        if (catName) {
            const catSpan = document.createElement('span');
            catSpan.textContent = 'Kategorija: ' + catName;
            meta.appendChild(catSpan);
        }

        main.appendChild(title);
        if (task.description) main.appendChild(desc);
        main.appendChild(meta);

        const actions = document.createElement('div');
        actions.className = 'task-card-actions';

        const editBtn = document.createElement('button');
        editBtn.className = 'btn btn-secondary btn-small';
        editBtn.textContent = 'Izmijeni';
        editBtn.addEventListener('click', () => fillTaskForm(task));

        const delBtn = document.createElement('button');
        delBtn.className = 'btn btn-secondary btn-small';
        delBtn.textContent = 'Obriši';
        delBtn.addEventListener('click', async () => {
            if (confirm(`Obrisati zadatak "${task.title}"?`)) {
                await apiCall(`/tasks/${task.id}`, { method: 'DELETE' });
                loadTasks();
            }
        });

        actions.appendChild(editBtn);
        actions.appendChild(delBtn);

        card.appendChild(main);
        card.appendChild(actions);
        container.appendChild(card);
    });
}

function fillTaskForm(task) {
    document.getElementById('taskId').value = task.id;
    document.getElementById('taskTitle').value = task.title;
    document.getElementById('taskDescription').value = task.description || '';
    document.getElementById('taskDueDate').value = task.due_date || '';
    document.getElementById('taskPriority').value = task.priority;
    document.getElementById('taskStatus').value = task.status;
    document.getElementById('taskCategory').value = task.category_id || '';

    document.getElementById('taskFormTitle').textContent = 'Izmijeni zadatak';
    document.getElementById('taskSubmitBtn').textContent = 'Sačuvaj izmjene';
    document.getElementById('cancelEditBtn').style.display = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetTaskForm() {
    document.getElementById('taskForm').reset();
    document.getElementById('taskId').value = '';
    document.getElementById('taskFormTitle').textContent = 'Novi zadatak';
    document.getElementById('taskSubmitBtn').textContent = 'Dodaj zadatak';
    document.getElementById('cancelEditBtn').style.display = 'none';
}

document.getElementById('cancelEditBtn').addEventListener('click', resetTaskForm);

document.getElementById('taskForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = document.getElementById('taskId').value;
    const payload = {
        title: document.getElementById('taskTitle').value.trim(),
        description: document.getElementById('taskDescription').value.trim(),
        due_date: document.getElementById('taskDueDate').value || null,
        priority: Number(document.getElementById('taskPriority').value),
        status: document.getElementById('taskStatus').value,
        category_id: document.getElementById('taskCategory').value || null,
    };

    if (!payload.title) return;

    if (id) {
        await apiCall(`/tasks/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
    } else {
        await apiCall('/tasks', { method: 'POST', body: JSON.stringify(payload) });
    }

    resetTaskForm();
    loadTasks();
});

document.getElementById('statusFilter').addEventListener('change', loadTasks);

let searchTimeout;
document.getElementById('searchInput').addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadTasks, 300);
});

// ---------- bootstrap ----------
// Nema endpointa za provjeru sesije, pa aplikacija uvijek starta na login ekranu.
showAuthView('login');
