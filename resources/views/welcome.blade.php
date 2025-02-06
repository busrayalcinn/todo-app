<?php
date_default_timezone_set('Asia/Istanbul');
use function Livewire\Volt\state;
use App\Models\Todo;

state(description: '', todos: fn() => Todo::all(), editingTodo: null, showAddForm: true);
state(plannedDate: null);

$toggleCalendar = function () {
    $this->plannedDate = !$this->plannedDate;
};

$setPlannedDate = function ($date) {
    $this->plannedDate = $date;
};

$addTodo = function () {
    $this->validate([
        'description' => 'required|min:1',
        'plannedDate' => 'nullable|date',
    ]);
    $this->todos->push(
        Todo::create([
            'description' => $this->description,
            'planned_at' => $this->plannedDate,
            'created_at' => now(),
        ]),
    );
    $this->description = '';
    $this->plannedDate = null;
    $this->showAddForm = true;
};

$deleteTodo = function ($id) {
    Todo::find($id)->delete();
    $this->todos = $this->todos->except($id);
};

$editTodo = function ($id) {
    $this->editingTodo = $id;
    $this->showAddForm = false;
    $todo = Todo::find($id);
    $this->description = $todo->description;
    $this->plannedDate = $todo->planned_at;
};

$updateTodo = function ($id) {
    $this->validate([
        'description' => 'required|min:1',
        'plannedDate' => 'nullable|date',
    ]);
    $todo = Todo::find($id);
    $todo->update([
        'description' => $this->description,
        'updated_at' => now(),
        'planned_at' => $this->plannedDate
    ]);
    $this->description = '';
    $this->plannedDate = null;
    $this->editingTodo = null;
    $this->showAddForm = true;
    $this->todos = $this->todos->map(function ($item) use ($todo) {
        if ($item->id === $todo->id) {
            return $todo;
        }
        return $item;
    });
};
?>

<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>Yapılacaklar</title>
    <style>
        .faded-text {
            opacity: 0.5;
            color: #666;
        }
        .todo-item {
            cursor: pointer;
            word-wrap: break-word;
            white-space: pre-wrap;
            max-width: 100%;
        }
        .planned-date-input::placeholder {
            
            color: #aaa;
        }
        .todo-window {
            width: 400px;
            height: 300px;
            overflow-y: auto;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .container {
            display: flex;
            justify-content: space-between;
        }
        .todo-list {
            width: 40%;
            margin-left: auto;
        }
    </style>
    <script>
        function openTodoDetails(description) {
            const newWindow = window.open("", "_blank", "width=400,height=300,scrollbars=yes");
            newWindow.document.write("<div class='todo-window'><h2>Not Detayları</h2>");
            newWindow.document.write("<p>" + description.replace(/(.{50})/g, "$1<br>") + "</p></div>");
        }
    </script>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>Yapılacaklar</title>
    <style>
        .faded-text {
            opacity: 0.6;
            color: #666;
        }
        .todo-item {
            cursor: pointer;
            word-wrap: break-word;
            white-space: pre-wrap;
            max-width: 100%;
        }
        .planned-date-input::placeholder {
            opacity: 0.5;
            color: #aaa;
        }
        .todo-window {
            width: 400px;
            height: 300px;
            overflow-y: auto;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .container {
            display: flex;
            justify-content: space-between;
        }
        .todo-list {
            width: 40%;
            margin-left: auto;
        }
    </style>
    <script>
        function openTodoDetails(description) {
            const newWindow = window.open("", "_blank", "width=400,height=300,scrollbars=yes");
            newWindow.document.write("<div class='todo-window'><h2>Not Detayları</h2>");
            newWindow.document.write("<p>" + description.replace(/(.{50})/g, "$1<br>") + "</p></div>");
        }
    </script>
</head>
<body>
    @volt
        <div class="container">
            <div>
                @if ($showAddForm)
                    <div class="input-area">
                        <h1 style="color:#0b105d;">Yapılacak iş ekle</h1>
                        <form wire:submit="{{ $editingTodo ? 'updateTodo(' . $editingTodo . ')' : 'addTodo' }}">
                            <input type="text" wire:model="description" placeholder="Yapılacak işi girin">
                            <label for="plannedDate">Yapılacak işin planlanan zamanı:</label>
                            <input type="datetime-local" id="plannedDate" wire:model="plannedDate" class="planned-date-input" placeholder="gg.aa.yyyy giriniz">
                            <button type="submit">{{ $editingTodo ? 'Düzenle' : 'Ekle' }}</button>
                        </form>
                    </div>
                @endif
            </div>
            <div class="todo-list">
                <h1 style="color:#0b105d;">Yapılacak işler</h1>
                <ul class="list-area">
                    @foreach ($todos as $todo)
                        <li>
                            <div class="updated-details faded-text">
                                @if ($todo->updated_at == $todo->created_at)
                                    <span>Yaratılan zaman: {{ optional($todo->created_at)->format('d-m-Y H:i') }}</span>
                                @else
                                    <span><ins>Güncellenen zaman:</ins> {{ optional($todo->updated_at)->format('d-m-Y H:i') }}</span>
                                @endif
                            </div>
                            <span class="todo-item" onclick="openTodoDetails('{{ $todo->description }}')">
                                {{ strlen($todo->description) > 50 ? wordwrap($todo->description, 50, "\n", true) : $todo->description }}
                            </span>
                            <br>
                            <span style="font-family:Cursive; color:#ffb47c; border:2px solid black; border-style:dotted; padding:4px; border-color:#8386ad;">
                                Planlanan zaman: {{ \Carbon\Carbon::parse($todo->planned_at)->format('d-m-Y H:i') }}
                            </span>
                            <div class="buttons">
                                <button type="button" wire:click="editTodo({{ $todo->id }})">Düzenle</button>
                                <button type="button" wire:click="deleteTodo({{ $todo->id }})">Sil</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endvolt
</body>
<script>
        let todoWindow = null;
        let offsetX = 0, offsetY = 0;

        // Not detay penceresini açma
        function openTodoDetails(description) {
            if (todoWindow) {
                todoWindow.remove();  // Var olan pencereyi kapat
            }

            // Yeni pencere (div) oluşturuluyor
            todoWindow = document.createElement('div');
            todoWindow.classList.add('todo-window');

            // İçeriği yazıyoruz
            todoWindow.innerHTML = `
                <h2>Not Detayları</h2>
                <p>${description.replace(/(.{50})/g, "$1<br>")}</p>
                <button class="close-btn" onclick="closeTodoWindow()">X</button>
            `;

            document.body.appendChild(todoWindow);

            // Taşınabilirlik için mouse eventi ekleyelim
            todoWindow.addEventListener('mousedown', startDrag);
            todoWindow.addEventListener('mouseup', stopDrag);
            todoWindow.addEventListener('mousemove', drag);
        }

        // Pencereyi kapatma
        function closeTodoWindow() {
            if (todoWindow) {
                todoWindow.remove();
                todoWindow = null;
            }
        }

        // Taşıma başlatma
        function startDrag(e) {
            offsetX = e.clientX - todoWindow.offsetLeft;
            offsetY = e.clientY - todoWindow.offsetTop;
            todoWindow.style.cursor = "move";  // Taşıma işlemi aktif
        }

        // Taşımayı durdurma
        function stopDrag() {
            todoWindow.style.cursor = "default";  // Normal kursor
        }

        // Pencereyi hareket ettirme
        function drag(e) {
            if (e.buttons === 1) {  // Sol tıklama ile taşımayı etkinleştir
                todoWindow.style.left = (e.clientX - offsetX) + 'px';
                todoWindow.style.top = (e.clientY - offsetY) + 'px';
            }
        }

        // Not ekleme
        function addTodo() {
            const todoInput = document.getElementById('todo-input');
            const todoList = document.getElementById('todo-list');
            const todoItem = document.createElement('li');
            todoItem.textContent = todoInput.value;

            // Tıklanınca detay penceresini aç
            todoItem.onclick = function() {
                openTodoDetails(todoInput.value);
            };

            todoList.appendChild(todoItem);
            todoInput.value = '';
        }
    </script>

</html>
