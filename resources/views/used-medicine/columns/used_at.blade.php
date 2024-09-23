
@php
$str =  explode("\\",$row->medicineBill->model_type)[2] ?? 'N/A';
$str= preg_replace('/(?<=\\w)(?=[A-Z])/'," $1", $str);
if (str_contains($str, 'Ipd')){
    $str = str_replace('Ipd','IPD',$str );
}
@endphp
{{ $str }}
