<!doctype html>
<html lang="nl"><body style="margin:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#222">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:20px 0"><tr><td align="center">
<table width="620" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e3e6e9">
    <tr><td style="background:#fff;border-bottom:3px solid #FF6600;padding:12px 20px">
        <table cellpadding="0" cellspacing="0"><tr><td style="width:34px;height:34px;background:#FF6600;color:#fff;font-weight:bold;font-size:20px;text-align:center;border-radius:6px">B</td><td style="padding-left:10px;font-weight:bold">Boels Industrial · materieelaanvraag</td></tr></table>
    </td></tr>
    <tr><td style="padding:20px">
        <p style="margin:0 0 10px">Beste collega's van <strong>{{ $a->depot_naam ?: 'depot '.$a->depot }}</strong>,</p>
        <p style="margin:0 0 14px">Voor {{ $a->soort === 'project' ? 'project' : 'contract' }} <strong>{{ $a->nummer }}</strong>@if($a->klant) ({{ $a->klant }})@endif hebben wij bij {{ $a->eigen_depot_naam ?: 'depot '.$a->eigen_depot }} het volgende materieel nodig @if($a->verhuurdatum), graag beschikbaar vanaf <strong>{{ $a->verhuurdatum->format('d-m-Y') }}</strong>@endif:</p>
        <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-size:14px">
            <tr style="background:#fafbfc;color:#7a8088;font-size:12px;text-transform:uppercase"><td style="border-bottom:1px solid #e3e6e9">Aantal</td><td style="border-bottom:1px solid #e3e6e9">Subgroep</td><td style="border-bottom:1px solid #e3e6e9">Omschrijving</td></tr>
            @foreach($a->regels as $r)
                <tr><td style="border-bottom:1px solid #eef0f2;font-weight:bold">{{ $r['aantal'] }}×</td><td style="border-bottom:1px solid #eef0f2">{{ $r['subgroep'] }}</td><td style="border-bottom:1px solid #eef0f2">{{ $r['omschrijving'] }}</td></tr>
            @endforeach
        </table>
        @if($a->opmerking)<p style="margin:14px 0 0;padding:10px;background:#fff7e6;border-radius:6px">{{ $a->opmerking }}</p>@endif
        @if($a->reactie_voor)<p style="margin:14px 0 0">Graag een reactie vóór <strong>{{ $a->reactie_voor->format('d-m-Y') }}</strong>.</p>@endif
        <p style="margin:18px 0 8px;font-weight:bold">Kunnen jullie dit leveren? Klik op één van de knoppen:</p>
        <table cellpadding="0" cellspacing="0"><tr>
            <td style="padding-right:8px"><a href="{{ $a->reactieUrl('akkoord') }}" style="display:inline-block;background:#2e7d32;color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:bold">Akkoord, alles</a></td>
            <td style="padding-right:8px"><a href="{{ $a->reactieUrl('deels') }}" style="display:inline-block;background:#ef6c00;color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:bold">Deels</a></td>
            <td><a href="{{ $a->reactieUrl('nee') }}" style="display:inline-block;background:#c62828;color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:bold">Nee</a></td>
        </tr></table>
        <p style="margin:14px 0 0;font-size:12px;color:#7a8088">Je bevestigt op de pagina die opent, met je naam. Inloggen is niet nodig. Vragen? Beantwoord deze mail, dan komt hij bij {{ $a->aanvrager_naam ?: 'de aanvrager' }}{{ $a->aanvrager_email ? ' ('.$a->aanvrager_email.')' : '' }}.</p>
        <p style="margin:16px 0 0">Met vriendelijke groet,<br>{{ $a->aanvrager_naam }}<br>{{ $a->eigen_depot_naam ?: 'Boels Industrial' }}</p>
    </td></tr>
</table>
</td></tr></table>
</body></html>
