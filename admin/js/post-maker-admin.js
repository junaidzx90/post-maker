jQuery(document).ready(function ($) {
	var loader = `<div id="loaderspin"> <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve"> <path opacity="0.2" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946 s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634 c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"></path> <path fill="#2271b1" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0 C22.32,8.481,24.301,9.057,26.013,10.047z"> <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.9s" repeatCount="indefinite"></animateTransform> </path> </svg> </div>`;


	$('#pm_upload_thumbnail').click(function (e) {
	  e.preventDefault();

	  var post_thumbnail;
  
	  if (post_thumbnail) {
		post_thumbnail.open();
		return;
	  }
	  // Extend the wp.media object
	  post_thumbnail = wp.media.frames.file_frame = wp.media({
		title: 'Select thumbnail',
		button: {
		  text: 'Select',
		},
		multiple: false,
	  });
  
	  // When a file is selected, grab the URL and set it as the text field's value
	  post_thumbnail.on('select', function () {
		var attachment = post_thumbnail.state().get('selection').first().toJSON();
		$('#pm_default_thumbnail').val(attachment.id);
		$('.pm_thumbnail_preview').html(
		  `<img style="width: 190px; margin: 10px 0; border: 2px solid #ddd; border-radius: 5px;" src="${attachment.url}">`
		);
	  });
	  // Open the upload dialog
	  post_thumbnail.open();
	});
  
	$('#pm_remove_thumbnail').on('click', function (e) {
	  e.preventDefault();
	  $('.pm_thumbnail_preview').html('');
	  $('#pm_default_thumbnail').val('');
	});
	
    $('#pm_default_author').selectize({
      placeholder: ' Select an author',
    });
    $('#pm_default_category').selectize({
      placeholder: ' Select categories',
      plugins: ['remove_button'],
    });
    $('#pm_default_tags').selectize({
      placeholder: ' Select tags',
      plugins: ['remove_button'],
    });

	// Add single template content
	$(document).on('click', '.add_new_template', function (e) {
		e.preventDefault();
		let timestamp = new Date().getTime();

		let textareaContent = `<div class="pm__template_content"> <textarea id="pm_post_contents_${timestamp}" name="pm_post_contents[]" class="widefat" rows="5"></textarea> <span class="pm_remove_template">+</span></div>`;
		$(this).parents('td').find('.pm__default_templates').append(textareaContent);

		let coonfig = {
			mediaButtons: true,
			tinymce: {
				wpautop: !1,
				toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
				toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
				keep_styles: !0
			},
			quicktags: true,
		}
		wp.editor.initialize('pm_post_contents_'+timestamp, coonfig);
	});
	// Remove template content
	$(document).on('click', '.pm_remove_template', function () {
		if (confirm('You will lose your saved template!')) {
			$(this).parents('.pm__template_content').remove();
		}
	});

	let temp_id = 2;
	$(document).on("click", "#pm_add_shortcode", function(e){
		e.preventDefault();
		
		let inputV = `<div class="shortcode_contents"><input data-id="${temp_id}" type="text" class="pmshortcode" placeholder="Keyword"><code>[pm-keyword-${temp_id}]</code><span class="pm_remove_shortcode">+</span></div>`;
		
		$(this).parents('td').find('#pm_shortcodes').append(inputV);
		temp_id++;
	});
	// Remove shortcode
	$(document).on('click', '.pm_remove_shortcode', function () {
		if (confirm('This keyword will not be accessible for post creation!')) {
			$(this).parents('.shortcode_contents').remove();
		}
	});

	$("#create_new_post").on("click", function(e){
		e.preventDefault();
		
		let title = $("input[name='pm_post_title']").val();
		let shortcodes = [];
		$("#pm_shortcodes").find("input.pmshortcode").each(function(){
			let data = {
				id: $(this).data("id"),
				content: $(this).val()
			}
			shortcodes.push(data);
		});

		let contents = [];
		$(".pm__default_templates").find("textarea").each(function(){
			let content = wp.editor.getContent( $(this).attr("id") );
			contents.push(content)
		});

		let thumbnail = $("#pm_default_thumbnail").val();
		let tags = $("#pm_default_tags").val();
		let categories = $("#pm_default_category").val();
		let author = $("#pm_default_author").val();

		let formData = {
			title: title,
			contents: contents,
			thumbnail: thumbnail,
			tags: tags,
			categories: categories,
			author: author,
			shortcodes: shortcodes
		}
		
		$.ajax({
			type: "post",
			url: pm_ajax.ajaxurl,
			data: {
				action: "pm_create_post",
				data: formData
			},
			beforeSend: ()=>{
				$("body").append(loader);
			},
			dataType: "json",
			success: function (response) {
				$(document).find("#loaderspin").remove();
				if(response.success){
					let alert = `<div id="pm_success_alert"> <div class="pm_alert_content"> <span class="pm_close_alert">+</span> <input type="url" id="preview_pm_link" value="${response.success}" readonly> <div class="pm_buttons"> <button id="pm_copy" class="button-primary pm_copy"><span class="pm_tooltiptext">Copy to clipboard</span> Copy Link</button> <a target="_blank" href="${response.success}" class="button-primary pm_open">Open Link</a> </div> </div> </div>`;
					$("body").append(alert);
				}
			}
		});
	});

	// Remove the alert
	$(document).on("click", ".pm_close_alert", function(){
		$(this).parents("#pm_success_alert").remove();
	});
	$(document).on("keyup", function(e){
		if(e.key === "Escape" || e.key === "x"){
			$(document).find("#pm_success_alert").remove();
		}
	});

	$(document).on("click", "#pm_copy", function(){
		var copyText = document.getElementById("preview_pm_link");
		copyText.select();
		copyText.setSelectionRange(0, 99999);
		navigator.clipboard.writeText(copyText.value);
		
		var tooltip = document.querySelector(".pm_tooltiptext");
		tooltip.innerHTML = "Copied";
		setTimeout(() => {
			tooltip.innerHTML = "Copy to clipboard";
		}, 1000);
	});
	
  });
  