<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class CalculationController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return response()->json(['error' => 'Invalid request. Expecting JSON.'], 400);
        }

        $request->validate(['expression' => 'required|string']);

        try {
            $expression = strtolower($request->expression);
            $expression = $this->replaceConstants($expression);

            $language = new ExpressionLanguage();
            $this->registerFunctions($language);

            $result = $language->evaluate($expression);

            Calculation::create([
                'user_id' => Auth::id(),
                'expression' => $request->expression,
                'result' => $result,
            ]);

            return response()->json(['result' => $result]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Expression evaluation failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function preview(Request $request)
    {
        if (!$request->expectsJson()) {
            return response()->json(['error' => 'Invalid request. Expecting JSON.'], 400);
        }

        $request->validate(['expression' => 'required|string']);

        try {
            $expression = strtolower($request->expression);
            $expression = $this->replaceConstants($expression);

            $language = new ExpressionLanguage();
            $this->registerFunctions($language);

            $result = $language->evaluate($expression);

            return response()->json(['result' => $result]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Expression evaluation failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function history(Request $request)
    {
        return response()->json(
            Calculation::where('user_id', Auth::id())->latest()->limit(10)->get()
        );
    }

    public function exportHistory()
    {
        $calculations = Calculation::where('user_id', Auth::id())->latest()->get();
        $pdf = Pdf::loadView('calculations.pdf', compact('calculations'));

        return $pdf->download('calculation_history.pdf');
    }

    public function exportCsv()
    {
        $calculations = Calculation::where('user_id', Auth::id())->latest()->get();

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=calculation_history.csv",
        ];

        return response()->stream(function () use ($calculations) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Expression', 'Result', 'Created At']);

            foreach ($calculations as $calc) {
                fputcsv($file, [
                    $calc->id,
                    $calc->expression,
                    $calc->result,
                    $calc->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    // Private Helper Methods
    private function replaceConstants(string $expression): string
    {
        $constants = [
            'pi' => pi(),
            'e' => exp(1),
            'phi' => (1 + sqrt(5)) / 2,
            'tau' => 2 * pi(),
        ];

        foreach ($constants as $key => $value) {
            $expression = preg_replace('/\b' . preg_quote($key, '/') . '\b/', $value, $expression);
        }

        return $expression;
    }


    private function registerFunctions(ExpressionLanguage $language): void
    {
        $language->register('sqrt', fn($x) => "sqrt($x)", fn($args, $x) => sqrt($x));
        $language->register('cbrt', fn($x) => "cbrt($x)", fn($args, $x) => pow($x, 1/3));
        $language->register('abs', fn($x) => "abs($x)", fn($args, $x) => abs($x));
        $language->register('floor', fn($x) => "floor($x)", fn($args, $x) => floor($x));
        $language->register('ceil', fn($x) => "ceil($x)", fn($args, $x) => ceil($x));
        $language->register('round', fn($x) => "round($x)", fn($args, $x) => round($x));
        $language->register('log', fn($x) => "log($x)", fn($args, $x) => log($x));
        $language->register('log10', fn($x) => "log10($x)", fn($args, $x) => log10($x));
        $language->register('exp', fn($x) => "exp($x)", fn($args, $x) => exp($x));
        $language->register('sin', fn($x) => "sin($x)", fn($args, $x) => sin($x));
        $language->register('cos', fn($x) => "cos($x)", fn($args, $x) => cos($x));
        $language->register('tan', fn($x) => "tan($x)", fn($args, $x) => tan($x));
        $language->register('asin', fn($x) => "asin($x)", fn($args, $x) => asin($x));
        $language->register('acos', fn($x) => "acos($x)", fn($args, $x) => acos($x));
        $language->register('atan', fn($x) => "atan($x)", fn($args, $x) => atan($x));
        $language->register('min', fn($x, $y) => "min($x,$y)", fn($args, $x, $y) => min($x, $y));
        $language->register('max', fn($x, $y) => "max($x,$y)", fn($args, $x, $y) => max($x, $y));
        $language->register('factorial', fn($n) => "factorial($n)", function ($args, $n) {
            if ($n < 0) throw new \Exception('Factorial undefined for negative numbers');
            return array_product(range(1, $n)) ?: 1;
        });
        $language->register('ncr', fn($n, $r) => "ncr($n,$r)", function($args, $n, $r) {
            if ($r > $n) return 0;
            $r = min($r, $n - $r);
            if ($r == 0) return 1;
            $numer = array_product(range($n, $n - $r + 1));
            $denom = array_product(range(1, $r));
            return $numer / $denom;
        });
        $language->register('npr', fn($n, $r) => "npr($n,$r)", function ($args, $n, $r) {
            if ($n < $r) throw new \Exception('Invalid input: n must be greater or equal to r');
            return array_product(range($n, $n - $r + 1));
        });

        $language->register('rad', fn($x) => "rad($x)", fn($args, $x) => deg2rad($x));
        $language->register('deg', fn($x) => "deg($x)", fn($args, $x) => rad2deg($x));
        $language->register('mean', fn(...$vals) => "mean(...)", fn($args, ...$vals) => array_sum($vals) / count($vals));
        $language->register('median', fn(...$vals) => "median(...)", function ($args, ...$vals) {
            sort($vals);
            $count = count($vals);
            $middle = intdiv($count, 2);
            return $count % 2 ? $vals[$middle] : ($vals[$middle - 1] + $vals[$middle]) / 2;
        });
        $language->register('variance', fn(...$vals) => "variance(...)", function ($args, ...$vals) {
            $mean = array_sum($vals) / count($vals);
            return array_sum(array_map(fn($x) => ($x - $mean) ** 2, $vals)) / count($vals);
        });
        $language->register('stddev', fn(...$vals) => "stddev(...)", fn($args, ...$vals) => sqrt($args['variance']($args, ...$vals)));
    }
}
