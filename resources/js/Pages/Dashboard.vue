<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const expression = ref('');
const result = ref(null);
const liveResult = ref(null);
const errorMessage = ref('');
const calculations = ref([]);
const favorites = ref(JSON.parse(localStorage.getItem('favorites') || '[]'));
const loading = ref(false);
const showFunctions = ref(false);

const calculate = async () => {
    loading.value = true;
    try {
        const response = await axios.post('/calculate', { expression: expression.value });
        result.value = response.data.result;
        errorMessage.value = '';
        fetchHistory();
    } catch (error) {
        result.value = null;
        errorMessage.value = error.response?.data?.message || 'Error calculating';
    } finally {
        loading.value = false;
    }
};

const fetchHistory = async () => {
    const response = await axios.get('/history');
    calculations.value = response.data || [];
};

const exportHistory = () => {
    window.location.href = '/export-history';
};

const exportCsv = () => {
    window.location.href = '/export-csv';
};

const addFavorite = () => {
    if (expression.value && !favorites.value.includes(expression.value)) {
        favorites.value.push(expression.value);
        localStorage.setItem('favorites', JSON.stringify(favorites.value));
    }
};

const removeFavorite = (fav) => {
    favorites.value = favorites.value.filter(f => f !== fav);
    localStorage.setItem('favorites', JSON.stringify(favorites.value));
};

const useFavorite = (fav) => {
    const existingCalc = calculations.value.find(calc => calc.expression === fav);
    if (existingCalc) {
        expression.value = existingCalc.expression;
        result.value = existingCalc.result;
        errorMessage.value = '';
    } else {
        expression.value = fav;
        calculate();
    }
};

const useHistory = (calc) => {
    expression.value = calc.expression;
    result.value = calc.result;
    errorMessage.value = '';
};

const livePreview = async () => {
    if (!expression.value.trim()) {
        liveResult.value = '';
        return;
    }
    try {
        const response = await axios.post('/calculate-preview', { expression: expression.value });
        liveResult.value = response.data.result;
    } catch {
        liveResult.value = '...';
    }
};

watch(expression, livePreview);

fetchHistory();
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Calculator</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <div class="mb-4">
                        <input v-model="expression" placeholder="Enter expression" class="border p-2 dark:bg-gray-700 dark:text-white w-full">

                        <div class="text-gray-500 dark:text-gray-400 mt-2">
                            Live Preview: <b>{{ liveResult }}</b>
                        </div>

                        <button @click="calculate" class="bg-blue-500 text-white px-4 py-2 mt-2" :disabled="loading">
                            {{ loading ? 'Calculating...' : 'Calculate' }}
                        </button>
                        <button @click="exportHistory" class="bg-green-500 text-white px-4 py-2 ml-2">Download PDF</button>
                        <button @click="exportCsv" class="bg-yellow-500 text-white px-4 py-2 ml-2">Download CSV</button>
                        <button @click="addFavorite" class="bg-purple-500 text-white px-4 py-2 ml-2">Add Favorite</button>
                        <button @click="showFunctions = !showFunctions" class="bg-gray-500 text-white px-4 py-2 ml-2">
                            {{ showFunctions ? 'Hide' : 'Show' }} Functions
                        </button>
                    </div>

                    <div v-if="result !== null" class="mt-4 text-green-500">Result: {{ result }}</div>
                    <div v-if="errorMessage" class="text-red-500">{{ errorMessage }}</div>

                    <!-- History Table -->
                    <h3 class="mt-6 font-semibold text-gray-900 dark:text-white">History</h3>
                    <table class="w-full mt-4 border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="border border-gray-300 dark:border-gray-600 px-4 py-2">Expression</th>
                            <th class="border border-gray-300 dark:border-gray-600 px-4 py-2">Result</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="calc in calculations" :key="calc.id"
                            @click="useFavorite(calc.expression)" class="cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700">
                            <td class="border px-4 py-2">{{ calc.expression }}</td>
                            <td class="border px-4 py-2">{{ calc.result }}</td>
                        </tr>
                        </tbody>
                    </table>

                    <!-- Favorites -->
                    <h3 class="mt-6 font-semibold text-gray-900 dark:text-white">Favorites</h3>
                    <ul class="list-disc ml-6">
                        <li v-for="fav in favorites" :key="fav">
                            <span class="cursor-pointer text-blue-500" @click="useFavorite(fav)">{{ fav }}</span>
                            <button class="ml-2 text-red-500" @click="removeFavorite(fav)">Remove</button>
                        </li>
                    </ul>

                    <!-- Functions List -->
                    <div v-if="showFunctions" class="mt-6 p-4 bg-gray-200 dark:bg-gray-700 rounded-lg">
                        <h3 class="font-semibold">Available Functions:</h3>
                        <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300">
                            <li><b>Basic:</b> +, -, *, /, %, ^</li>
                            <li><b>Roots:</b> sqrt(x), cbrt(x)</li>
                            <li><b>Rounding:</b> abs(x), floor(x), ceil(x), round(x)</li>
                            <li><b>Trig:</b> sin(x), cos(x), tan(x), asin(x), acos(x), atan(x)</li>
                            <li><b>Logarithms:</b> log(x), log10(x), exp(x)</li>
                            <li><b>Min/Max:</b> min(x, y), max(x, y)</li>
                            <li><b>Factorial:</b> factorial(x)</li>
                            <li><b>Combinations & Permutations:</b> nCr(n, r), nPr(n, r)</li>
                            <li><b>Statistics:</b> mean(...), median(...), variance(...), stddev(...)</li>
                            <li><b>Finance:</b> compound_interest(p, r, n, t), loan_payment(p, r, n)</li>
                            <li><b>Conversions:</b> meters_to_feet(x), celsius_to_fahrenheit(x), rad(x), deg(x)</li>
                            <li><b>Constants:</b> PI, E, phi, tau</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
