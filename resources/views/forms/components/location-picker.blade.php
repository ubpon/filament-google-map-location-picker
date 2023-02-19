<?php 

    $map_id = "map" .  \Illuminate\Support\Str::slug($getStatePath(), "_");
    $libraries = config('filament-google-map-location-picker.libraries');

?>

<script
        src="https://maps.googleapis.com/maps/api/js?key={{config('filament-google-map-location-picker.google_map_key')}}&libraries={{config('filament-google-map-location-picker.libraries')}}&v=weekly&language={{app()->getLocale()}}">
</script>
<script>
    function {{$map_id}}googleMapPicker(config) {
        return {
            value: config.value,
            markerLocation: {},
            zoom: config.zoom,
            {{$map_id}}init: function () {
                var locationCenter = {!! $getLocationCenter() !!};
                var valueLocation = null;

                if (this?.value instanceof Object) {
                    valueLocation = this?.value;
                } else {
                    valueLocation = JSON.parse(this?.value);
                }

                var center = {
                    lat: valueLocation?.lat || locationCenter.lat,
                    lng: valueLocation?.lng || locationCenter.lng
                }

                var map = new google.maps.Map(this.$refs.{{$map_id}}map, {
                    center: center,
                    zoom: this.zoom,
                    zoomControl: false,
                    ...config.controls
                });


                var marker = new google.maps.Marker({
                    map
                });


                if (valueLocation?.lat && valueLocation?.lng) {
                    marker.setPosition(valueLocation);
                }

                map.addListener('click', (event) => {
                    this.markerLocation = event.latLng.toJSON();
                });

                if (config.controls.searchBoxControl) {
                    const input = this.$refs.{{$map_id}}pacinput;
                    const searchBox = new google.maps.places.SearchBox(input);
                    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
                    searchBox.addListener("places_changed", () => {
                        input.value = ''
                        this.markerLocation = searchBox.getPlaces()[0].geometry.location
                    })
                }

                if (config.hasDrawing) {
                    const drawingManager = new google.maps.drawing.DrawingManager({
                        drawingMode: google.maps.drawing.OverlayType.MARKER,
                        drawingControl: true,
                        drawingControlOptions: {
                        position: google.maps.ControlPosition.TOP_CENTER,
                        drawingModes: [
                            google.maps.drawing.OverlayType.POLYGON,
                            google.maps.drawing.OverlayType.RECTANGLE,
                        ],
                        },
                        markerOptions: {
                        icon: "https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png",
                        },
                        circleOptions: {
                        fillColor: "#ffff00",
                        fillOpacity: 1,
                        strokeWeight: 5,
                        clickable: false,
                        editable: true,
                        zIndex: 1,
                        },
                    });

                    drawingManager.setMap(map);
                }

                this.$watch('markerLocation', () => {
                    let position = this.markerLocation;
                    this.value = position;
                    marker.setPosition(position);
                    map.panTo(position);
                })
            }

        }
    }
</script>

<x-forms::field-wrapper
        :id="$getId()"
        :label="$getLabel()"
        :label-sr-only="$isLabelHidden()"
        :helper-text="$getHelperText()"
        :hint="$getHint()"
        :hint-icon="$getHintIcon()"
        :required="$isRequired()"
        :state-path="$getStatePath()">

    <div wire:ignore x-data="{{$map_id}}googleMapPicker({
            value: $wire.entangle('{{ $getStatePath() }}'),
            zoom: {{$getDefaultZoom()}},
            controls: {{$getMapControls()}},
            hasDrawing: {{str_contains('drawing', $libraries) ? true : false}}
        })" x-init="{{$map_id}}init()">
        @if($isSearchBoxControlEnabled())
            <input x-ref="{{$map_id}}pacinput" type="text" placeholder="Search Box"/>
        @endif
        <div x-ref="{{$map_id}}map" class="w-full" style="min-height: 40vh"></div>
    </div>
</x-forms::field-wrapper>
