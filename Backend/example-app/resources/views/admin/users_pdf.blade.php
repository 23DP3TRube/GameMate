<!DOCTYPE html>
<html lang="lv">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; }
  h1   { font-size: 18px; margin-bottom: 4px; }
  .sub { color: #6b7280; font-size: 10px; margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #7c3aed; color: #fff; padding: 7px 10px; text-align: left; font-size: 10px; }
  td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background: #f9f9f9; }
  .badge-admin { background: #ede9fe; color: #7c3aed; padding: 2px 7px; border-radius: 10px; font-weight: 700; }
  .badge-user  { background: #f3f4f6; color: #6b7280; padding: 2px 7px; border-radius: 10px; }
  .footer { margin-top: 16px; color: #9ca3af; font-size: 9px; text-align: right; }
</style>
</head>
<body>
  <h1>GameMate — Lietotāju saraksts</h1>
  <p class="sub">Eksportēts: {{ now()->format('d.m.Y H:i') }} · Kopā: {{ count($users) }} lietotāji</p>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Vārds</th>
        <th>E-pasts</th>
        <th>Gamertag</th>
        <th>Platforma</th>
        <th>Reģions</th>
        <th>Loma</th>
        <th>Reģistrēts</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $u)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $u['name'] }}</td>
        <td>{{ $u['email'] }}</td>
        <td>{{ $u['gamertag'] }}</td>
        <td>{{ $u['platform'] }}</td>
        <td>{{ $u['region'] }}</td>
        <td>
          @if($u['is_admin'])
            <span class="badge-admin">Admins</span>
          @else
            <span class="badge-user">Lietotājs</span>
          @endif
        </td>
        <td>{{ $u['created_at'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <p class="footer">GameMate eksports · {{ now()->format('d.m.Y') }}</p>
</body>
</html>
