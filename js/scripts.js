let todos = [];
let chart; // Pasta grafiği referansı

// Görevleri Sunucudan Getir
async function fetchTodos() {
    try {
        const response = await fetch('http://localhost/todo_app/tasks.php?action=fetch');
        todos = await response.json();
        renderTodos();
        updateChart(); // Grafiği güncelle
    } catch (error) {
        console.error('Görevler yüklenirken hata:', error);
    }
}

function formatTime(seconds) {
    const days = Math.floor(seconds / (24 * 3600));
    seconds %= 24 * 3600;
    const hours = Math.floor(seconds / 3600);
    seconds %= 3600;
    const minutes = Math.floor(seconds / 60);
    seconds %= 60;

    let formattedTime = '';
    if (days > 0) formattedTime += `${days} gün `;
    if (hours > 0) formattedTime += `${hours} saat `;
    if (minutes > 0) formattedTime += `${minutes} dakika `;
    if (seconds > 0 || formattedTime === '') formattedTime += `${seconds} saniye`;

    return formattedTime.trim();
}

// Görevleri Listele
function renderTodos() {
    const todoList = document.getElementById('todos');
    todoList.innerHTML = '';

    todos.forEach((todo, index) => {
        const listItem = document.createElement('li');
        listItem.className = `todo ${todo.done ? 'done' : ''}`;

        // Görev metni
        const textContainer = document.createElement('div');
        textContainer.className = 'todo-content';
        textContainer.textContent = todo.text;
        listItem.appendChild(textContainer);

        // Tamamlama zamanı
        if (todo.done && todo.completion_time) {
            const timeContainer = document.createElement('button');
            timeContainer.className = 'completion-time'; // Tamamla/Geri Al butonunun CSS'sini kullanır
            timeContainer.textContent = `${formatTime(todo.completion_time)}`;
            listItem.appendChild(timeContainer);
        }

        // Butonlar
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'todo-buttons';

        const toggleButton = document.createElement('button');
        toggleButton.className = `toggleButton ${todo.done ? 'incomplete' : 'complete'}`;
        toggleButton.innerHTML = todo.done
            ? '<span class="icon">↩</span> Geri Al'
            : '<span class="icon">✔</span> Tamamla';
        toggleButton.onclick = () => toggleTodo(index);
        buttonContainer.appendChild(toggleButton);

        const deleteButton = document.createElement('button');
        deleteButton.textContent = 'Sil';
        deleteButton.className = 'deleteButtonKucuk';
        deleteButton.onclick = () => deleteTodo(index);
        buttonContainer.appendChild(deleteButton);

        listItem.appendChild(buttonContainer);
        todoList.appendChild(listItem);
    });
}


// Pasta Grafiğini Güncelle
function updateChart() {
    const completedCount = todos.filter(todo => todo.done).length;
    const incompleteCount = todos.length - completedCount;

    const chartData = [completedCount, incompleteCount];

    if (chart) {
        // Grafik mevcutsa güncelle
        chart.data.datasets[0].data = chartData;
        chart.update();
    } else {
        // Grafik yoksa oluştur
        const ctx = document.getElementById('taskChart').getContext('2d');
        chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Tamamlanmış', 'Tamamlanmamış'],
                datasets: [{
                    data: chartData,
                    backgroundColor: ['#4caf50', '#f44336'],
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
}

// Diğer Fonksiyonlar (addTodo, toggleTodo, editTodo, deleteTodo) aynı kalır.
// Her işlemden sonra updateChart() çağrılır.

async function addTodo() {
    const input = document.getElementById('todoInput');
    const text = input.value.trim();
    if (!text) return alert('Görev boş olamaz!');

    try {
        const response = await fetch('http://localhost/todo_app/tasks.php?action=add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `text=${encodeURIComponent(text)}`
        });
        const newTask = await response.json();
        todos.push(newTask);
        renderTodos();
        updateChart(); // Grafiği güncelle
        input.value = '';
    } catch (error) {
        console.error('Görev eklenirken hata:', error);
    }
}

async function toggleTodo(index) {
    const todo = todos[index];
    todo.done = !todo.done;

    try {
        await fetch('http://localhost/todo_app/tasks.php?action=update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${todo.id}&done=${todo.done ? 1 : 0}`
        });
        renderTodos();
        updateChart(); // Grafiği güncelle
    } catch (error) {
        console.error('Görev güncellenirken hata:', error);
    }
}

async function deleteTodo(index) {
    const todo = todos[index];

    try {
        await fetch('http://localhost/todo_app/tasks.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${todo.id}`
        });
        todos.splice(index, 1);
        renderTodos();
        updateChart(); // Grafiği güncelle
    } catch (error) {
        console.error('Görev silinirken hata:', error);
    }
}

// Sayfa Yüklendiğinde Başlat
document.addEventListener('DOMContentLoaded', () => {
    fetchTodos();

    // Pasta Grafiği Konteyneri Oluştur
    const container = document.getElementById('chartContainer');
});

async function deleteCompletedTodos() {
    try {
        const response = await fetch('http://localhost/todo_app/tasks.php?action=deleteCompleted', {
            method: 'POST',
        });
        const result = await response.json();

        if (result.status === 'success') {
            todos = todos.filter(todo => !todo.done);
            renderTodos();
        } else {
            console.error('Tamamlanan görevler silinirken hata:', result.error);
        }
    } catch (error) {
        console.error('Tamamlanan görevler silinirken hata:', error);
    }
}

async function deleteAllTodos() {
    try {
        await fetch('http://localhost/todo_app/tasks.php?action=deleteAll');
        todos = [];
        renderTodos();
    } catch (error) {
        console.error('Tüm görevler silinirken hata:', error);
    }
}

// Çıkış yap
function logout() {
    window.location.href = "logout.php";
}