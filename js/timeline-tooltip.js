 function showTooltip( x, y, contents ) {
	  jQuery('<div id="ws-tooltip" style="padding-left: 4px; padding-right: 4px;border-radius:4px; -moz-border-radius:4px; -webkit-border-radius: 4px;text-align:center;">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y - 16,
			left: x + 10,
			border: '2px solid #def',
			padding: '2px',
			'background-color': '#f9fcff',
			opacity: 0.80
	  }).appendTo("body").fadeIn(200);
 }
 var previousPoint = null;
 jQuery("#ws-graph-timeline").bind("plothover", function (event, pos, item) {
		if (item) {
			 if (previousPoint != item.dataIndex) {
					previousPoint = item.dataIndex;
					jQuery("#ws-tooltip").remove();
					var x = item.datapoint[0];
					var y = item.datapoint[1];
					var datePoint = new Date();
					datePoint.setTime( x );
					var dateString; dateString = datePoint.getMonth() + 1;
					dateString = dateString + "/" + datePoint.getFullYear();
					showTooltip(item.pageX, item.pageY, "<strong>" + y + "</strong><br /><small>" + dateString + "</small>");
			  }
		 } else {
			 jQuery("#ws-tooltip").remove();
			 previousPoint = null;
		}
 });
