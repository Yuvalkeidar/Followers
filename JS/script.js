$(document).ready(function() {
	$('.follow-checkbox').change(function(e) {

		var selectedRow = $(this).closest('tr');
		var userID = selectedRow.find('.user_id').text();
		var followNum = selectedRow.find('.follow_num').text();

		if ($(this).hasClass('follow-checked')) {

			$.ajax({
				type: 'POST',
				url: "/deleteFollow",
				xhrFields: {
					withCredentials: true
				},
				data: {
					user_id: userID
				},
				error: function(response) {
					alert('Error occured');
					console.log(response);
				},
				success: function(result) {
					$(this).find('.follow-checkbox').toggleClass('follow-checked');
					$(this).find('.follow_num')[0].innerHTML = parseInt($(this).find('.follow_num').text()) - 1;
				}.bind(selectedRow)
			});

		} else {

			$.ajax({
				type: 'POST',
				url: "/addFollow",
				xhrFields: {
					withCredentials: true
				},
				data: {
					user_id: userID
				},
				error: function(response) {
					alert('Error occured');
					console.log(response);
				},
				success: function(result) {
					$(this).find('.follow-checkbox').toggleClass('follow-checked');
					$(this).find('.follow_num')[0].innerHTML = parseInt($(this).find('.follow_num').text()) + 1;
				}.bind(selectedRow)
			});
		}

	});
});