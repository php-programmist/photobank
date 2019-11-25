$(document).ready(function () {
	$('#batch_brand').change(function () {
		var parentSelect = $(this);
		
		$.ajax({
			url: "/batch/api/get-models-by-brand",
			type: "GET",
			dataType: "JSON",
			data: {
				brand_id: parentSelect.val()
			},
			success: function (children) {
				var childSelect = $("#batch_model");
				
				// Remove current options
				childSelect.html('');
				
				// Empty value ...
				childSelect.append('<option value>Выберите модель ' + parentSelect.find("option:selected").text() + ' ...</option>');
				
				$.each(children, function (key, child) {
					childSelect.append('<option value="' + child.id + '">' + child.name + '</option>');
				});
			},
			error: function (err) {
				alert("An error ocurred while loading data ...");
			}
		});
	});
	
	$('#batch_serviceCategory').change(function () {
		var parentSelector = $(this);
		
		$.ajax({
			url: "/batch/api/get-services-by-category",
			type: "GET",
			dataType: "JSON",
			data: {
				category_id: parentSelector.val()
			},
			success: function (children) {
				var childSelect = $("#batch_service");
				
				// Remove current options
				childSelect.html('');
				
				// Empty value ...
				childSelect.append('<option value>Выберите услугу категории ' + parentSelector.find("option:selected").text() + ' ...</option>');
				
				$.each(children, function (key, child) {
					childSelect.append('<option value="' + child.id + '">' + child.name + '</option>');
				});
			},
			error: function (err) {
				alert("An error ocurred while loading data ...");
			}
		});
	});
});