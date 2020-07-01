$(document).ready(function() {




$.ajax({
        type: 'post',
        url: "AjaxStatDevis",
        data : {"COM" : 7},    
    success: function(data){
        
        dataSet = JSON.parse(data);
        console.log(dataSet);



      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);

      function drawVisualization() {
        // Some raw data (not necessarily accurate)
        console.log(dataSet);
        var data = google.visualization.arrayToDataTable([
          ['commercial',                       'Total',         'En attente',              'Valide'],
          [dataSet[0].nom,       dataSet[0].Total,         dataSet[0].ATN ,       dataSet[0].VLD],
          [dataSet[1].nom,       dataSet[1].Total,         dataSet[1].ATN ,       dataSet[1].VLD],
          [dataSet[2].nom,       dataSet[2].Total,         dataSet[2].ATN ,       dataSet[2].VLD],
          [dataSet[3].nom,       dataSet[3].Total,         dataSet[3].ATN ,       dataSet[3].VLD],
        ]);

        var options = {
          title : 'Devis envoyés 15 derniers jours',
          vAxis: {title: 'Devis'},
          hAxis: {title: 'commerciaux'},
          seriesType: 'bars',
          series: {5: {type: 'line'}},
          colors: 
          [
            '03A9F4',
            'FF8F00',
            '#4CAF50',

          ]        };

        var chart = new google.visualization.ComboChart(document.getElementById('charts-Devis'));
        chart.draw(data, options);
      }




























        },

    error: function (err) {
    alert('error: ' + err);}
    })







   




})