function drawVisualization() {
    // Create and populate the data table.
    var data = google.visualization.arrayToDataTable([
      ['Name', 'Manager', 'Tooltip'],
      ['Global', null, 'World Domination'],
      [{ v: 'Australia', f: 'Australia<br/><font color="red">John Doe<br/>John Doe<br/>John Doe<br/></font><hr/>336 Members<br/>' }, 'Global', null],
      [{ v: 'Melbourne', f: 'Melbourne<br/><font color="red">John Doe<br/>John Doe<br/>John Doe<br/></font><hr/>273 Members<br/>' }, 'Australia', null],
      [{ v: 'Sydney', f: 'Sydney<br/><font color="red">John Doe<br/>John Doe<br/>John Doe<br/></font><hr/>53 Members<br/>' }, 'Australia', null],
      [{ v: 'Agile', f: 'Agile<br/><font color="red">John Doe<br/>John Doe<br/>John Doe<br/></font><hr/>53 Active<br/>70 inactive<br/>' }, 'Melbourne', null],
    ]);

    // Create and draw the visualization.
    new google.visualization.OrgChart(document.getElementById('visualization')).
        draw(data, { allowHtml: true });
}


google.setOnLoadCallback(drawVisualization);

