<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background: #f3f3f3; }
        .right { text-align: right; }
    </style>
</head>
<body>

<h1>Reçu / Relevé de Prêt</h1>

<p><strong>Employé :</strong> {{ $user->name }}</p>
<p><strong>Date :</strong> {{ now()->format('d/m/Y') }}</p>

<hr>

<h3>Détails du prêt</h3>
<table>
    <tr><th>Montant</th><td class="right">{{ number_format($pret->amount, 2) }} DT</td></tr>
    <tr><th>Taux</th><td>{{ $pret->interestRate }} %</td></tr>
    <tr><th>Durée</th><td>{{ $pret->dureeMonths }} mois</td></tr>
    <tr><th>Statut</th><td>{{ $pret->status }}</td></tr>
</table>

<h3>Mensualités</h3>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($mensualites as $i => $m)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($m->dueDate)->format('d/m/Y') }}</td>
            <td class="right">{{ number_format($m->amount, 2) }} DT</td>
            <td>{{ $m->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<br>

<p>
    <strong>Total payé :</strong>
    {{ number_format($mensualites->where('status','Payé')->sum('amount'), 2) }} DT
</p>

<p>
    <strong>Reste à payer :</strong>
    {{ number_format(
        $pret->amount - $mensualites->where('status','Payé')->sum('amount'),
        2
    ) }} DT
</p>

</body>
</html>
