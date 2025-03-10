# Calculator
Calculator
A robust mathematical expression calculator implemented with Laravel and Vue.js, allowing users to input mathematical expressions, evaluate them, preview results in real-time, and manage calculation history and favorites.

Implemented Backend Features (Laravel):
Expression Evaluation:
Evaluates user-provided mathematical expressions securely with Symfony ExpressionLanguage.
Supports complex expressions, including nested operations.

Available Mathematical Functions and Constants
Basic Arithmetic: +, -, *, /, %, ^ (power)
Roots: sqrt(x), cbrt(x)
Rounding: abs(x), floor(x), ceil(x), round(x)
Trigonometry: sin(x), cos(x), tan(x), asin(x), acos(x), atan(x)
Logarithms: log(x), log10(x), exp(x)
Min/Max: min(x,y), max(x,y)
Factorials: factorial(x)
Combinatorics: nCr(n,r), nPr(n,r)
Statistical Functions: mean(...), median(...), variance(...), stddev(...)
Financial Calculations: compound_interest(p,r,n,t), loan_payment(p,r,n)
Conversions: meters_to_feet(x), celsius_to_fahrenheit(x), rad(x), deg(x)
Constants: PI, E, phi, tau

Implemented Improvements
Live Preview:
Users receive a real-time calculation preview as they type.
Calculation History:
Stores calculation results in the database tied to authenticated users.
Provides exports to PDF and CSV formats.
Favorite Expressions:
Users can mark expressions as favorites, stored locally in the browser’s local storage for quick reuse.
Robust Constant Replacement:
Error Handling:
Informative messages are returned to users when invalid expressions are provided.

Instructions:

git clone https://github.com/mantas-gagelas/calculator
cd calculator
composer install
copy .env.example .env     
php artisan key:generate
php artisan migrate
npm install                
npm run dev                
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan serve

