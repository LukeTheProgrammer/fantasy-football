

var gridCols = {set: false},
	gridMultiSelect = 0;

//JQuery Plugin that will take a form, serialize it's field values, then create a JSON object of the array
(function ($) {
	$.fn.serializeJSON = function () {
		var json = {};
		jQuery.map($(this).serializeArray(), function(n, i){
			(json[n['name']] === undefined) ? json[n['name']] = n['value'] : json[n['name']] += ',' + n['value'];
		});
		return json;
	};
})( jQuery );

$.extend($.fn.fmatter, {
	actionFormatter: function(cellvalue, options, rowObject) {
		var retVal = "";
		retVal += "<span class=\'icon-trigger action-trigger icn-page\' title=\'View Patient Claims\' data-pid=\'" + cellvalue + "\'></span>";
		return retVal;
	}
});

$.extend($.fn.fmatter, {
	detailsFormatter: function(cellvalue, options, rowObject) {
		return '<span class="btn btn-info btn-xs js-patient-claims" data-pid="' + cellvalue + '" data-part="' + rowObject[1].replace('-','') + '" style="margin:2px;">Details</span>';
	}
});

$.extend($.fn.fmatter, {
	statusFormatter: function(cellvalue, options, rowObject){
		if (cellvalue === 'D') {
			return '<span class=\"redBold\">D</span>';
		} else if (cellvalue === 'R') {
				return '<span class=\"orangeBold\">R</span>';
		} else if (cellvalue === 'A') {
				return '<span class=\"greenBold\">A</span>';
		}else {	return '';}
	}
});




$(document).ready(function(){
	var grid = $('#AnalyticsList'),
	scrubSearch = function(){
		var frm = $('form#SearchForm').serializeJSON();
		for(var i in frm){
			var val = frm[i];
			// if value has no length, remove the key
			if(val.length === 0 || val === '__/__/____'){
				delete frm[i];
			}
			//  if(!isNaN(val-0) && parseInt(val) === 0){
			//     delete frm[i];
			// }
		}
		return frm;
	};


	/*
	 * FUNCTION bindActionHandlers
	 * This method is called within the gridLoadInit() method, and is used to
	 * bind event handlers to the action icons of new records
	 */
	var bindActionHandlers = function () {
		$('.icn-page').on('click', function(e){
			e.preventDefault();
			var row = $(this).closest('tr'),
				rowId = row.attr("id"),
				recId = $(this).data('pid'),
				acoId = grid.jqGrid('getCell',rowId,'CMSACONUM');
			var cdlg = $("#ClaimsDlg").dialog({
				modal: true,
				height: 620,
				width: '80%',
				resizable: false,
				draggable: true,
				create: function(){
					//$('body').addClass('stop-scrolling');
					$('span.ui-icon-closethick').html("");
				},
				title: 'Patient Claims Information',
				buttons:{
					'Close':function(){
						//$('body').removeClass('stop-scrolling');
						$(this).html('').dialog('close');
					}
				},
				open: function(event, ui) {
					//console.log(recId + ' ' + acoId);
					showPatientClaimsDialog(recId,acoId);
				},
				close: function(event,ui){
					$('body').removeClass('stop-scrolling');
				}
			});
		});
	};
	/*
	 * FUNCTION populateGrid
	 * Used as the 'datatype' attribute of the jqGrid config object, this method
	 * is used to handle the ajax calls and data manipulation needed to populate
	 * data within our jqGrid instance.
	 * @postdata (object) - this is the object passed as the 'postData' attribute
	 * 						of our jqGrid instance.
	 */
	var populateGrid = function (postdata) {
		$.blockUI({
			  themedCSS: {
					width:  '535'
				},
	        theme:     true,
	        title:    'Please Wait',
	        message:  '<div style="font-size:18px; float:left;margin:12px; padding:15px; width:450px;"><img src="/assets/images/ajax-loader2.gif" border="0" style="float:left; margin-right:10px;" /><div style="width:300px; float:right;">Retrieving Claims...<br />this could take a few minutes</div></div>'
    	});

		var serachParams = JSON.stringify(scrubSearch());

		$.ajax({
			url: '/com/healthendeavors/aco/analyticsCombined.cfc',
			data: $.extend(true, {}, postdata, {search: serachParams}),
			type:'POST',
			dataType:"json",
			cache: false,
			success: function(d,r,o){
				if(d.success){
					// Set search params

					grid.data('searchParams', serachParams);

					// If loading for the first time, let's find out to which
					// array positions our columns map.
					if(!gridCols.set){
						for(var i in d.data.COLUMNS){
							gridCols[d.data.COLUMNS[i]] = parseInt(i);
						}
						gridCols.set = true;
					}
					grid.jqGrid('setGridParam',{remapColumns:[
						gridCols['PATIENTID'],
						gridCols['CLAIM_SOURCE'],
						gridCols['HICN'],
						gridCols['MBI'],
						gridCols['FIRSTNAME'],
						gridCols['LASTNAME'],
						gridCols['GENDER'],
						gridCols['DOB'],
						gridCols['STATUS'],
						gridCols['PRIMARY_PRACTICE'],
						gridCols['SUBTIN'],
						gridCols['DIVISION'],
						gridCols['TOTALCLAIMS'],
						gridCols['TOTALCLAIMAMT'],
						gridCols['CMSACONUM'],
						gridCols['PATIENTID']
					]});
					
					// Convert server data to local format and load it
					var localData = [];
					for(var row = 0; row < d.data.DATA.length; row++) {
						var rowData = {};
						for(var col = 0; col < d.data.COLUMNS.length; col++) {
							rowData[d.data.COLUMNS[col]] = d.data.DATA[row][col];
						}
						localData.push(rowData);
					}
					
					// Clear and reload with formatted data
					grid.jqGrid('clearGridData');
					for(var idx = 0; idx < localData.length; idx++) {
						grid.jqGrid('addRowData', idx+1, localData[idx]);
					}
				} else {
					$.unblockUI();
					alert(d.message);
					if(d.expired){ document.location.href='/login.cfm?logout'; }
				}

				$('.js-patient-claims').on('click',{searchParams: grid.data('searchParams')},function(e){
					var claims = new Claims($(this).data('pid'),e.data.searchParams,$(this).data('part'))
					claims.buildPopup();
				});
			},
			error: function(){
				$.unblockUI();
				alert('An error has occured. No Records Found');
			}
		});
	};

	/*
	 * FUNCTION gridLoadInit
	 * This method is tied to grid loads, through the gridComplete attribute
	 * Any time the data is refreshed in the grid (page load, paging, manual reset, etc)
	 * then this method is run to set event bindings on any created DOM.
	 */
	var gridLoadInit = function () {
			bindActionHandlers();
			grid.jqGrid('groupingToggle','AnalyticsListghead_0');
			//grid.jqGrid('setGridWidth',900);

			//var filterval = $("#AddFilter").val();
			//var filterval = $('.partCheck:checked').val();
			var filterval = [];
			$('.partCheck').each(function() {
				if($(this).is(':checked')) {
					filterval.push($(this).val())
				}
			});
			filterval = String(filterval)

			var gridcount = grid.jqGrid('getGridParam', 'records');

			if (gridcount > 0) {
				$("#ExportFull").show();
				$("#ExportFullBtn").removeAttr("href").attr("href","analyticsexportCombined.cfm?reporttype="+filterval);
			}
			else {
				$("#ExportFull").hide();
				$("#ExportFullBtn").removeAttr("href");
			}

			$.unblockUI();
	};

	/*
	 * FUNCTION gridUnloader
	 * Called from within the populateGrid method, this method is to unbind
	 * any event handlers created during grid load, by the gridLoadInit method.
	 */
	var gridUnloader = function () {

	};

	grid.jqGrid({
		toolbar:[true,"top"],
		width:$(window).width() -50,
		sortable: false,
		height: 500,
		loadui: 'disable',
		altRows: false,
		gridComplete: gridLoadInit,
		deepempty: true,
		rowNum: 10,
		rowList: [10, 25, 50, 100],
		viewrecords: true,
		sortname: 'totalClaimAmt',
		sortorder: 'DESC',
		footerrow: true,
		userDataOnFooter: true,
 		/*grouping:true,
		groupingView : {
				groupField : ['NAME'],
				groupSummary : [true],
				groupColumnShow : [false],
				groupText : ['<b>{0}</b> ({1})'],
				groupCollapse : true,
				groupDataSorted : true
				},*/
		colModel: [
			{name:'Action',index:'PATIENTID',label:'Action', width: 40, fixed: true, sortable: false, align: 'center', formatter: 'actionFormatter'},
			{name:'CLAIM_SOURCE',index:'CLAIM_SOURCE',label:'Source',width:40,fixed:true,sortable:false},
			{name:'HICN',label:'HICN',width:70,fixed:true,sortable:false},
			{name:'MBI',label:'MBI',width:70,fixed:true,sortable:false},
			{name:'FIRSTNAME',label:'First', width:60,sortable:false},
			{name:'LASTNAME',label:'Last', width:60,sortable:false},
			{name:'GENDER',label:'Sex', width:15, align: 'center',sortable:false},
			{name:'DOB',label:'DOB',sortable:false,width:55,align:'center', formatter:'date',formatoptions:{srcformat:'Y-m-d',newformat:'m/d/Y'} },
			{name:'STATUS',label:'Status',sortable:false,width:40, align: 'center',formatter:'statusFormatter'},
			{name:'PRIMARY_PRACTICE',label:'Practice', width:80,sortable:false},
			{name:'SUBTIN',label:'Sub-TIN', width:60,sortable:false},
			{name:'DIVISION',label:'Div.', width:60,sortable:false},
			{name:'TOTALCLAIMS',label:'Total Claims', align:'center', formatter:'integer', width:45,summaryType: 'sum',sortable:false},
			{name:'TOTALCLAIMAMT',label:'Total of Claims', align:'center', formatter:'currency', formatoptions:{prefix:'$'},summaryType: 'sum',width:55,sortable:false},
			{name:'CMSACONUM', hidden:true},
			{name:'Details', index:'PATIENTID', label:'Details', width:60, fixed: true, sortable: false, align: 'center', formatter: 'detailsFormatter'}
		],
		prmNames:{page:"pageIndex",sort:"sortCol",order:"sortDir",rows:"pageSize"},
		postData:{method:"GetHighClaims",returnFormat:"JSON"},
		datatype: 'local',
		pager: '#AnalyticsPager',
		data: [], // Initialize with empty local data
		jsonReader: {
			id: "5",
			root: "rows",
			page: "page",
			total: "total",
			records: "records",
			cell: ""
		}
	});

	$('#t_AnalyticsList').append('<button id=\"BtnExcelExport\">Excel Export</button>').addClass('customToolbar');

	// click handler of the 'Excel' button
	$('#BtnExcelExport').button({icons: {primary: 'icn-excel'}}).on('click', function(ev){
		ev.preventDefault();
		blockUIForDownload();
//		return false;
	});

	var searchFrm = $('#SearchForm');
	searchFrm.on('submit', function(ev){
		ev.preventDefault();
		// Call populateGrid directly since we're always in local mode
		populateGrid({});

		return false;
	});

	var fileDownloadCheckTimer;
	function blockUIForDownload() {
		var token = new Date().getTime(); //use the current timestamp as the token value
		var vals = scrubSearch();
		location.href="/secure/aco/_analyticsExportCombined.cfm?exporttype=excel&token="+token+'&vals='+JSON.stringify(vals);
		$.blockUI({theme:true, title:'Please Wait', message: '<h1>Generating Report...</h1>(Could take a few seconds to a few minutes)' });
		fileDownloadCheckTimer = window.setInterval(function () {
			var cookieValue = $.cookie('FILEDOWNLOADTOKEN');
			if (cookieValue == token)
				{finishDownload();}
		}, 1000);
	}
	function finishDownload() {
		window.clearInterval(fileDownloadCheckTimer);
		$.cookie('FILEDOWNLOADTOKEN', null); //clears this cookie value
		$.unblockUI();
	}
	function showPatientClaimsDialog(pid,acoid){
        $.blockUI({
            theme:     true,
            title:    'Please Wait',
            message:  '<h1>Retrieving Claims...</h1>'
        });
		$("#ClaimsDlg").load("/secure/aco/_patientclaims.cfm",{pid: pid,acoid: acoid, showbtns: 0});
	}

	$('#DrugLookup, #drugCodeLookup').on('click', function(){
		$('#DrugLookupDlg').dialog('open');
	});


	$(window).resize(function () {
		 $("#AnalyticsList").setGridWidth($(window).width() -50);
	});

	$("#DrugLookupDlg").dialog({
		modal: true,
		autoOpen: false,
		draggable: true,
		resizable: true,
		closeOnEscape: true,
		height: 480,
		width: 665,
		hide: { effect: 'fadeOut', duration: 300 },
		title: 'Search Drug Code Lookup',
		close: function() {
			$(this).html('');
		},
		buttons: {
			'Cancel': function(){
				$(this).dialog('close');
			},
			'Search': function(){
				if ($('#LupDrugCode').val()=='' && $('#LupDrugName').val()=='') {
					alert('Please correct the following: \n\n A valid drug code or name is required');
				} else {
					$(this).load('/secure/aco/_drugLookup.cfm',{lupDrugCode:$('#LupDrugCode').val(), lupDrugName:$('#LupDrugName').val()});
				}
			}
		},
		open: function(event, ui) {
			$(this).load('/secure/aco/_drugLookup.cfm');
		}
	});
});

var Claims = function(patientid, searchParams, part){
	this.patientid = patientid || 0;
	this.searchParams = searchParams || '{}';
	this.part = part || '';
	this.method = 'getPatientClaims';
	this.popup = null;
};

Claims.prototype.buildPopup = function(){
	this.getClaims(this);
};

Claims.prototype.getClaims = function(){
	var self = this;
	var searchParams = JSON.parse(this.searchParams);

	if(this.part != ''){
		searchParams.addFilter = this.part;
	}

	$.ajax({
		url: '/com/healthendeavors/aco/analyticsCombined.cfc',
		data: {
			method: this.method,
			search: JSON.stringify(searchParams),
			patientid: this.patientid
		},
		type:'POST',
		dataType:"json",
		cache: false,
		success: function(data,status,xhr){
			if(!data['success']){
				alert(data['message']);

				return;
			}

			self.createPopup(data['data']);
		},
		error: function(data,status,xhr){
		}
	});
}

Claims.prototype.createPopup = function(data){
	var html = '';

	for(row in data){
		html += this.claimHtml(data[row]);
	}

	this.openPopup(html);
}

Claims.prototype.openPopup = function(html){
	var windowHeight = $(window).height() - 120;

	bootbox.alert({
	    message: '<div class="claim-body" style="height: 100%; max-height: ' + windowHeight + 'px; overflow-x:auto;">' + html + '</div>',
	    size: 'large'
	});
}

Claims.prototype.claimHtml = function(claim){
	var spacer = '<div class="row" style="width:auto;"><div class"col-xs-12" style="height:20px"></div></div>';
	var fromDate = new Date(claim['CLAIM FROM DATE']);
	var toDate = new Date(claim['CLAIM THRU DATE']);

	var html = '' +
	'<div class="container-fluid">' +
		'<div class="panel-body">' +
			'<div class="row" style="width:auto;">' +
				'<div class="col-sm-3">' +
					'<b>Claim ID</b><br>' +
					claim['CLAIM ID'] +
					'<br>'+
				'</div>' +
				'<div class="col-sm-3">' +
					'<b>Claim Amount</b><br>' +
					'$' + claim['CLAIM AMOUNT'].toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') +
					'<br>'+
				'</div>' +
				'<div class="col-sm-3">' +
					'<b>Date(s)</b><br>' +
					(fromDate.getTime() == toDate.getTime() ? this.dateFormat(toDate) : this.dateFormat(fromDate) + ' – ' + this.dateFormat(toDate)) +
					'<br>'+
				'</div>' +
				'<div class="col-sm-3">' +
					'<b>Claim Type</b><br>' +
					claim['CLAIM TYPE'] +
				'</div>' +
			'</div>' +
			spacer +
			'<div class="row" style="width:auto;">' +
				'<div class="col-sm-3">' +
					'<b>Attending Provider</b><br>' +
					claim['ATTENDING PROVIDER NAME'] + '<br>' +
				'</div>' +
				'<div class="col-sm-3">' +
					'<b>Attending Provider NPI</b><br>' +
					claim['ATTENDING NPI'] + '<br>' +
				'</div>' +
				'<div class="col-sm-3">' +
					'<b>Operating Provider</b><br>' +
					claim['OPERATING PROVIDER NAME'] + '<br>' +
				'</div>' +
				'<div class="col-sm-3">' +
					'<b>Operating Provider NPI</b><br>' +
					claim['OPERATING PROVIDER NPI'] + '<br>' +
				'</div>' +
			'</div>' +
			spacer +
			'<div class="row" style="width:auto;">' +
				'<div class="col-sm-3">' +
					'<b>Principal Diagnosis</b><br>' +
					claim['PRINCIPAL ICD DESC.'] + '<br>' +
					'<b>ICD-10 Code:</b> ' + claim['PRINCIPAL ICD'] +
				'</div>' +
				'<div class="col-sm-3">' +
					'<b>Admitting Diagnosis</b><br>' +
					claim['ADMIT ICD DESC.'] + '<br>' +
					'<b>ICD-10 Code:</b> ' + claim['ADMITTING ICD'] +
				'</div>' +
				'<div class="col-sm-3">' +
					'<b>Admission Source</b><br>' +
					claim['ADMISSION SOURCE DESC.'] + '<br>' +
					'<b>Code:</b> ' + claim['ADMISSION SOURCE CODE'] +
				'</div>' +
				'<div class="col-sm-3">' +
					'<b>Admission Type</b><br>' +
					claim['ADMISSION TYPE DESC.'] + '<br>' +
					'<b>Code:</b> ' + claim['ADMISSION TYPE CODE'] +
				'</div>' +
			'</div>' +
			spacer +
			'<div class="row" style="width:auto;">' +
				'<div class="col-sm-12">' +
					'<b>HCPCS/CPT Codes:</b> ' + claim['HCPCS/CPT CODE'] +
				'</div>' +
			'</div>' +
			'<div class="row" style="width:auto;">' +
				'<div class="col-sm-12">' +
					'<b>Additional ICD Codes:</b> ' + claim['ADDITIONAL ICD CODE'] +
				'</div>' +
			'</div>' +
		'</div>' +
	'</div>' +
	'<div class="rowBottomBorder"></div>';

	return html;
}

Claims.prototype.dateFormat = function(date){
	var year = date.getFullYear();
	var month = date.getMonth()+1;
	var day = date.getDate();

	month = month <= 9 ? '0' + month : month;
	day = day <= 9 ? '0' + day : day;

	return month + '/' + day + '/' + year;
}

var examplePayload = {
    "message":"",
    "pageIndex":"1",
    "success":true,
    "data":{
        "COLUMNS":["PATIENTID","CLAIM_SOURCE","PRIMARY_PRACTICE","PRIMARY_PRACTICE_TIN","DIVISION","SUBTIN","HICN","MBI","FIRSTNAME","LASTNAME","GENDER","DOB","STATUS","DISCHARGESTATUSCODE","DISCHARGEDESCRIPTION","TOTALCLAIMS","TOTALCLAIMAMT"],
        "DATA":[['a','b','c'], ['d','e','f']],
    },
    "expired":false,
    "pageCount":10.0,
    "recordCount":100.0,
    "userdata":{"DOB":"Totals:","TOTALCLAIMS":169,"TOTALCLAIMAMT":5542751.33}
};

