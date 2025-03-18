<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { CheckCircle, Trash2, Pencil } from 'lucide-vue-next'

interface Todo {
    id: number
    name: string
    completed: boolean
    created_at: string
    completed_at?: string | null
}

const form = useForm({ name: '' })
const todos = ref<Todo[]>([])

const showEditModal = ref(false)
const editTask = ref<Todo | null>(null)
const editName = ref('')

const fetchTodos = async () => {
    try {
        const response = await axios.get('/items')
        todos.value = response.data.map((todo: Todo) => ({
            ...todo,
            created_at: new Date(todo.created_at).toLocaleString(),
            completed_at: todo.completed_at ? new Date(todo.completed_at).toLocaleString() : null
        }))
    } catch (error) {
        console.error('Błąd pobierania zadań:', error)
    }
}

onMounted(() => {
    fetchTodos()
})

const submit = async () => {
    await form.post(route('item.store'))
    form.reset('name')
    fetchTodos()
}

const markCompleted = async (todo: Todo) => {
    await axios.put(route('item.update', todo.id), {
        completed: true,
        completed_at: new Date().toISOString()
    })
    fetchTodos()
}

const deleteTodo = async (id: number) => {
    await axios.delete(route('item.destroy', id))
    fetchTodos()
}

const openEditModal = (todo: Todo) => {
    editTask.value = todo
    editName.value = todo.name
    showEditModal.value = true
}

const updateTask = async () => {
    if (!editTask.value) return
    try {
        await axios.put(route('item.update', editTask.value.id), { name: editName.value })
        showEditModal.value = false
        editTask.value = null
        fetchTodos()
    } catch (error) {
        console.error('Błąd edycji zadania:', error)
    }
}

const completedTodos = computed(() => {
    return todos.value
        .filter(todo => todo.completed)
        .sort((a, b) => {
            const dateA = a.completed_at ? new Date(a.completed_at).getTime() : 0;
            const dateB = b.completed_at ? new Date(b.completed_at).getTime() : 0;
            return dateB - dateA;
        });
});

const uncompletedTodos = computed(() => todos.value.filter(todo => !todo.completed))
</script>

<template>
    <Head title="Lista zadań" />

    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900 p-4 text-gray-900 dark:text-white transition">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 max-w-6xl w-full mx-auto">
            <!-- Niewykonane zadania -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow-lg transition flex flex-col h-full">
                <h3 class="text-lg font-semibold mb-4 text-center lg:text-left">Niewykonane zadania</h3>
                <div class="overflow-auto max-h-[calc(100vh-220px)] pb-4">
                    <ul>
                        <li v-for="todo in uncompletedTodos" :key="todo.id"
                            class="border-b border-gray-300 dark:border-gray-700 py-2 flex justify-between items-center">
                            <div class="flex-1 pr-4">
                                <div class="font-medium break-all">{{ todo.name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ todo.created_at }}</div>
                            </div>
                            <div class="flex items-center gap-2 mr-5">
                                <button @click="markCompleted(todo)" class="text-green-500 hover:text-green-400 transition">
                                    <CheckCircle />
                                </button>
                                <button @click="openEditModal(todo)" class="text-blue-400 hover:text-blue-300 transition">
                                    <Pencil />
                                </button>
                                <button @click="deleteTodo(todo.id)" class="text-red-400 hover:text-red-300 transition">
                                    <Trash2 />
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Formularz dodawania -->
            <div class="flex flex-col items-center justify-center order-first lg:order-none">
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg transition w-full max-w-sm">
                    <form @submit.prevent="submit">
                        <label class="block mb-2 font-semibold text-center">Nowe zadanie:</label>
                        <input v-model="form.name" required placeholder="Wpisz treść zadania"
                               class="w-full border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 rounded px-3 py-2 text-gray-900 dark:text-white"
                        />
                        <button type="submit"
                                class="mt-2 bg-blue-500 hover:bg-blue-400 transition text-white px-4 py-2 rounded w-full">
                            Dodaj zadanie
                        </button>
                    </form>
                </div>
            </div>

            <!-- Wykonane zadania -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow-lg transition flex flex-col h-full">
                <h3 class="text-lg font-semibold mb-4 text-center lg:text-left">Wykonane zadania</h3>
                <div class="overflow-auto max-h-[calc(100vh-220px)] pb-4">
                    <ul>
                        <li v-for="todo in completedTodos" :key="todo.id"
                            class="border-b border-gray-300 dark:border-gray-700 py-2 flex justify-between items-center">
                            <div class="flex-1 pr-4">
                                <div class="line-through break-all text-gray-500 dark:text-gray-400">{{ todo.name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Utworzone: {{ todo.created_at }}
                                </div>
                                <div v-if="todo.completed_at" class="text-sm text-gray-500 dark:text-gray-400">
                                    Zakończone: {{ todo.completed_at }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mr-5">
                                <CheckCircle class="text-gray-600 dark:text-gray-500" />
                                <button @click="deleteTodo(todo.id)" class="text-red-400 hover:text-red-300 transition">
                                    <Trash2 />
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Popup edycji zadania -->
        <div v-if="showEditModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg max-w-md w-full transition">
                <h3 class="text-xl font-semibold mb-4">Edytuj zadanie</h3>
                <input type="text" v-model="editName"
                       class="w-full border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-3 py-2 mb-4"
                />
                <div class="flex justify-end gap-2">
                    <button @click="showEditModal = false" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white px-4 py-2 rounded transition">
                        Anuluj
                    </button>
                    <button @click="updateTask" class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded transition">
                        Zapisz
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

