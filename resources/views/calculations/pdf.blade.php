<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculation History</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; word-wrap: break-word; }
        th, td { border: 1px solid black; padding: 6px; text-align: left; word-break: break-word; overflow-wrap: break-word; }
        th { background-color: #f2f2f2; }
        td.expression { max-width: 200px; white-space: normal; word-wrap: break-word; }
    </style>
</head>
<body>
<h2>Calculation History</h2>
<table>
    <thead>
    <tr>
        <th style="width: 5%;">#</th>
        <th style="width: 50%;">Expression</th>
        <th style="width: 20%;">Result</th>
        <th style="width: 25%;">Date</th>
    </tr>
    </thead>
    <tbody>
    @foreach($calculations as $index => $calc)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td class="expression">{{ $calc->expression }}</td>
            <td>{{ $calc->result }}</td>
            <td>{{ $calc->created_at->format('Y-m-d H:i:s') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
