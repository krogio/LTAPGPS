
<!-- PHP SERVER SIDE CODE START-->
<?php
// make sure this directory exists and is writeable. file will be created automatically upon first fetch
$loc = dirname(__FILE__).'/sqlite_db/coord.db';
$db = new SQLite3($loc,SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
$raw = file_get_contents('php://input');
$raw = preg_replace('/\\x00/','',$raw);
$data = json_decode($raw);

if (!empty($data) && is_object($data) && property_exists($data,'lat') && property_exists($data,'lon')){
    if(file_exists($loc)) echo 'exists!'.chr(0xa);
    $src = 'SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'coordinates\'';
    $res = $db->querySingle($src);
    if (count($res)==0){
            $db->exec('CREATE TABLE coordinates (latitude TEXT, longitude TEXT, time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, added TIMESTAMP DEFAULT CURRENT_TIMESTAMP ) ');
		}

		$regex = '/^(|\-)([0-9]{2,3}\.[0-9]{0,8})$/';

if (preg_match($regex,$data->lat) && preg_match($regex,$data->lon) )
	{
		$lat = $data->lat;
		$lon = $data->lon;
	}
	$ins = 'INSERT INTO coordinates (latitude,longitude) VALUES (\''.SQLite3::escapeString($lat).'\',\''.SQLite3::escapeString($lon).'\')';
	$db->exec($ins);
	die();
}
?>
<!-- PHP SERVER SIDE CODE END-->

<!DOCTYPE html>
<html>
  <head>
	  
    <!-- jQuery library -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <!--Leaflet Plugin-->
	  
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css"
      integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ=="
      crossorigin=""
    />
    <script
      src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"
      integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw=="
      crossorigin=""
    ></script>

	  <!-- KML Plugin -->
	  <script src="layer/vector/KML.js"></script>

    <!-- Latest compiled and Bootsrap minified CSS -->
    <link
      rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
    />
    <!--Bootstrap Datables plugin-->
    <link
      rel="stylesheet"
      href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap.min.css"
    />
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
	  <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>
    
    <!--Favicon-->
    <link rel="shortcut icon" href="favicon.ico" />

    <!-- No Bots-->
    <meta name="robots" content="noindex, nofollow">
  </head>
  
  <body>
    <div class="container">
      <!--Logo-->
      <?php include("templates/logo.php");?>
      <!--Logo-->
      <div class="row">
			<div class="col-md-6" id="map" style="height:400px;"></div>
			<div class="col-md-6">
				<table
					id="dtBasic"
					class="table table-hover table-bordered table-condensed"
					cellspacing="0"
				>
					<thead>
					<th>Latitude</th>
					<th>Longitude</th>
					<th>Time Added</th>
          <th>Details</th>
					</thead>
					<tbody>
					<?php $result = $db->query('SELECT latitude,longitude,added FROM
					coordinates ORDER BY added DESC') or die($db->errorInfo()[2]);; 
					
					while($obj =
					$result->fetchArray()){ 
						
						$new_time = date("Y-m-d H:i:s", strtotime("+2 hours",strtotime($obj['added'])));

						echo '
					<tr onclick="getFirst(this);">
						<td>' . $obj['latitude']. '</td>
						'. '
						<td>' . $obj['longitude'] .'</td>
						' . '
            <td>' . $new_time .'</td>
            ' . '
            <td><button style="margin:auto;border-radius: 30px;background-image: linear-gradient(to right, #f66819 , #f66819);border-style: none;color: white;font-weight: bold;padding: 5px;font-size: 10px;margin: 0px 0px 0px 20px;"onclick="getFirst(this)">View</button></td>
					</tr>
					'; } ?>
					</tbody>
				</table>
			</div>
		</div>
    <h3 id=address>Select Co-ordinates from the table to display address</h3>
	</div>
  </body>
</html>

<script>
var map = L.map('map').setView([0,0], 4);
L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {attribution: '<a href="https://osm.org/copyright">OSM</a>'}).addTo(map);

<?php
    if($result = $db->query('SELECT latitude,longitude FROM coordinates')){
    echo ' var latlngs = [ ';
    while($obj = $result->fetchArray()){
    	if (!is_array($obj) || !isset($obj['latitude']) || !isset($obj['longitude']) || empty($obj['latitude']) || empty($obj['longitude'])) continue;
    	echo '["'. $obj['latitude'].'","'.$obj['longitude'].'"],';
    }
    echo ']; ';
    } else
     echo('//'.$db->lastErrorMsg().chr(0xa));  
	 echo($data);
?>

var polyline = L.polyline(latlngs, {color: '#073763'}).addTo(map);
map.fitBounds(polyline.getBounds());

var Icon = L.icon({
    iconUrl: 'favicon.png',
    

    iconSize:     [20, 20], // size of the icon   
    iconAnchor:   [10, 10], // point of the icon which will correspond to marker's location
    popupAnchor:  [0, 0] // point from which the popup should open relative to the iconAnchor
});

L.marker([-25.940619, 28.144731], {icon: Icon}).addTo(map);


$(document).ready(function() {
    $('#dtBasic').DataTable({
		"order": [[ 2, "desc" ]]
	});
} );

tableLastRow();

function tableLastRow(){
var table = document.getElementById("dtBasic");
var tableLastLat = table.rows[table.rows.length+1-table.rows.length].children[0].innerHTML;
var tableLastLon = table.rows[table.rows.length+1-table.rows.length].children[1].innerHTML;

var Truck = L.icon({
    iconUrl: 'truck.png',
    

    iconSize:     [50, 50], // size of the icon   
    iconAnchor:   [20, 20], // point of the icon which will correspond to marker's location
    popupAnchor:  [0, 0] // point from which the popup should open relative to the iconAnchor
});
L.marker([tableLastLat, tableLastLon], {icon: Truck}).addTo(map);

}

var track = new L.KML("boundary.kml");
		track.on("loaded", function(e) {
			map.fitBounds(e.target.getBounds());
		});
		map.addLayer(track);

	
function getFirst(row){
	lat = row.children[0].innerHTML;
	lon = row.children[1].innerHTML;
  
  reverseGeo(lat,lon);
    
	var popup = L.popup()
    .setLatLng([lat, lon])
    .setContent("Lat: " + lat + " <br> " + "Lon: " + lon)
    .openOn(map);

	var circle = L.circle([lat, lon], {
    color: 'white',
    fillColor: '#fff',
    fillOpacity: 0.5,
    radius: 25
	}).addTo(map);
	
}

//REVERSE GEOIP(NOMINATIM) API CODE START SK
function reverseGeo(lat, lon){
var request = new XMLHttpRequest()
var getUrl = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat='+lat+'&lon='+lon;
request.open('GET', getUrl, true)
request.onload = function() {
  // Begin accessing JSON data here
  var data = JSON.parse(this.response);

  if (request.status >= 200 && request.status < 400) 
    {
      document.getElementById('address').innerHTML = data.display_name;
    }
  
  else{
    console.log("Nominatim API error");
  }
}

request.send()
}
//REVERSE GEOIP(NOMINATIM) API CODE END SK
</script>

</body>
</html>
