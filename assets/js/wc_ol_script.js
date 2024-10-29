var ebdn_reload_page_after_ajax = false;
jQuery(function ($) {

	$(document).on("click", ".ebdn-order-info", function () {
		var id = $(this).attr('id').split('-')[1];
		$.ebdn_show_order(id);
		return false;
	});

	$.ebdn_show_order = function (id) {
		$('<div id="ebdn-dialog' + id + '"></div>').dialog({
			dialogClass: 'wp-dialog',
			modal: true,
			title: "AffiliateImporterEb Info (ID: " + id + ")",
			open: function () {
				$('#ebdn-dialog' + id).html('Please wait, data loads..');
				var data = {'action': 'ebdn_order_info', 'id': id};

				$.post(ajaxurl, data, function (response) {
					//console.log('response: ', response);
					var json = jQuery.parseJSON(response);
					//console.log('result: ', json);

					if (json.state === 'error') {

						console.log(json);

					} else {
						//console.log(json);
						$('#ebdn-dialog' + json.data.id).html(json.data.content.join('<br/>'));
					}

				});


			},
			close: function (event, ui) {
				$("#ebdn-dialog" + id).remove();
			},
			buttons: {
				Ok: function () {
					$(this).dialog("close");
				}
			}
		});

		return false;

	};

});

