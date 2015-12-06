var app = angular.module("toiletFinder", ['snap']);
app.controller("MapController" , ["$scope", "$rootScope", "$http", function($scope, $rootScope, $http){

    $scope.snapOptions = {
        touchToDrag: false
    };

    $scope.defaultCenter = new google.maps.LatLng( 42.3601, -71.0589);//Boston, Massachusetts
    $scope.MapOptions = {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDefaultUI: true,
        panControl: true,
        zoom: 13,
        zoomControl: false
    };

    $scope.map = new google.maps.Map(document.getElementById('map'), $scope.MapOptions);
    $scope.markers = [];
    $scope.icon = 'img/pooicon.png';

    $scope.addMarker = function(place){

        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(place.lat,place.lng),
            map: $scope.map,
            icon: $scope.icon
        });
        $scope.markers.push(marker);
        google.maps.event.addListener(marker, "click", function () {
            $rootScope.$broadcast("markerClicked", {
                place: place,
                marker: marker
            });
        });
    };
    $scope.$on("markerAdded", function(event, args){
        $scope.addMarker(args.place);
        $scope.map.panTo(new google.maps.LatLng(args.place.lat, args.place.lng));
    });
    
    $scope.getLocation = function(){
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position){
                //found location successfully
                var location = new google.maps.LatLng(position.coords.latitude, position.coords.longitude)
                $scope.map.setCenter(location);
                $rootScope.$broadcast("locationFound", {
                    location: location
                });
            }, 
            function(error){
                //couldn't find location
                console.log(error);
                $scope.map.setCenter($scope.defaultCenter);
            });
        }
        else {
            //location service not available
            console.log("Geolocation is not supported by this browser.");
            $scope.map.setCenter($scope.defaultCenter);
        }
    }

    $scope.loadMarkers = function(){
        $http.get("markers.php")
        .then(function (response) {
            //on success
            var data = response.data;
            angular.forEach(data, function(value, key){
                $scope.addMarker(value);
            });
        });
    }

    
    $scope.getLocation();
    $scope.loadMarkers();
}]);

app.controller("DetailController", ["$scope", "$rootScope", "$http", "snapRemote", function($scope, $rootScope, $http, snapRemote){
    $scope.$on("markerClicked", function(event, args){
        $scope.place = args.place;
        snapRemote.open("right");
    });
    
}]);

app.controller("AddController", ["$scope", "$rootScope", "$http", "snapRemote", function($scope, $rootScope, $http, snapRemote){

    $scope.place;
    $scope.defaultCenter = new google.maps.LatLng(42.3601, -71.0589);//Boston, Massachusetts
    $scope.MapOptions = {
        center: $scope.defaultCenter,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDefaultUI: true,
        panControl: true,
        zoom: 13,
        zoomControl: false
    };
    $scope.map = new google.maps.Map(document.getElementById('formMap'), $scope.MapOptions);

    $scope.marker = new google.maps.Marker({
        position: $scope.defaultCenter,
        map: $scope.map,
        draggable: true
    });

    $scope.search = new google.maps.places.Autocomplete(document.getElementById('search'));

    google.maps.event.addListener($scope.map, "click", function(event){
        $scope.marker.setPosition(event.latLng);
    });

    $scope.search.addListener("place_changed", function() {
        var place = $scope.search.getPlace();
        $scope.marker.setPosition(place.geometry.location);
        $scope.map.panTo(place.geometry.location);
    });
    $scope.$on("locationFound", function(event, args){
        $scope.map.setCenter(args.location);
        $scope.marker.setPosition(args.location);
    });

    $scope.add = function(){
        var pos = $scope.marker.getPosition();
        var place = {
            lat: pos.lat(),
            lng: pos.lng(),
            name: $scope.name,
            directions: $scope.directions,
            notes: $scope.notes
        }
        $http.post("addMarker.php", place)
        .then(function(response){
            //on success
            $rootScope.$broadcast("markerAdded", {
                place: place
            });
            snapRemote.close();
            $scope.name = "";
            $scope.directions = "";
            $scope.notes = "";
        },function(response){
            //on error
            $scope.error = true;
            console.log(response);
        });
    }
}]);
