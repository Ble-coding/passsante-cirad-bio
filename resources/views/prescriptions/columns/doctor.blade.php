<div class="d-flex align-items-center">
    <div class="image image-mini me-3">
        <a href="{{url('doctors'.'/'.$row->patient->doctor->id)}}">
            <div>
                <img src="{{$row->patient->doctor->doctorUser->image_url}}" alt=""
                     class="user-img image rounded-circle object-contain">
            </div>
        </a>
    </div>
    <div class="d-flex flex-column">
        <a href="{{route('doctors_show',$row->patient->doctor->id)}}"
           class="mb-1 text-decoration-none">{{$row->patient->doctor->doctorUser->full_name}}</a>
        <span>{{$row->patient->doctor->doctorUser->email}}</span>
    </div>
</div>
