<!doctype html>
<html lang="nl"><body style="margin:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#222">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:20px 0"><tr><td align="center">
<table width="620" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e3e6e9">
    <tr><td style="border-bottom:3px solid #FF6600;padding:12px 20px"><table cellpadding="0" cellspacing="0"><tr><td style="width:34px;height:34px;background:#FF6600;color:#fff;font-weight:bold;font-size:20px;text-align:center;border-radius:6px">B</td><td style="padding-left:10px;font-weight:bold">Boels Industrial · reactie op je aanvraag</td></tr></table></td></tr>
    <tr><td style="padding:20px">
        <p style="margin:0 0 10px"><strong>{{ $a->gereageerd_door }}</strong> van {{ $a->depot_naam ?: 'depot '.$a->depot }} heeft gereageerd op je aanvraag voor {{ $a->nummer }}: <strong>{{ $a->label() }}</strong>.</p>
        @php($toeg = $a->toegezegdPerSubgroep())
        <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-size:14px">
            <tr style="background:#fafbfc;color:#7a8088;font-size:12px;text-transform:uppercase"><td>Subgroep</td><td align="right">Gevraagd</td><td align="right">Toegezegd</td></tr>
            @foreach($a->regels as $r)
                <tr><td style="border-bottom:1px solid #eef0f2">{{ $r['subgroep'] }} {{ $r['omschrijving'] }}</td><td align="right" style="border-bottom:1px solid #eef0f2">{{ $r['aantal'] }}</td><td align="right" style="border-bottom:1px solid #eef0f2;font-weight:bold">{{ $toeg[$r['subgroep']] ?? 0 }}</td></tr>
            @endforeach
        </table>
        @if($opmerking)<p style="margin:14px 0 0;padding:10px;background:#fff7e6;border-radius:6px">{{ $opmerking }}</p>@endif
        <p style="margin:16px 0 0"><a href="{{ route('voorraad.show', $a->nummer) }}" style="color:#FF6600;font-weight:bold">Open de voorraadcheck voor {{ $a->nummer }}</a>@if($a->status !== 'toegezegd') om de rest te regelen @endif.</p>
    </td></tr>
</table>
</td></tr></table>
</body></html>
