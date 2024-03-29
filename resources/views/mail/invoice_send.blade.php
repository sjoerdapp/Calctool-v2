<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">

	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
		<title>Factuur {{ $project_name }}</title>
	</head>

	@if ($preview)
	<script lang="text/javascript">
		$(document).ready(function() {
			var selected_other_contacts = [];
        	$('#multiple-contact').multiselect({
        		nonSelectedText: 'Overige contacten',
			    onChange: function(element, checked) {
			        var brands = $('#multiple-contact option:selected');
			        
			        $(brands).each(function(index, brand){
			            selected_other_contacts.push([$(this).val()]);
			        });

			        console.log(selected_other_contacts);
			    }
        	});
			$('#sendmail').click(function(){
				$.post("/invoice/sendmail", {
					invoice: {{ $invoice_id }},
					contacts: selected_other_contacts
				}, function(data){
					var json = data;
					if (json.success) {
						$('#mailsent').show();
					}
				});
			});
		});
	</script>
	@endif

	<body style="margin:0; margin-top:30px; margin-bottom:30px; padding:0; width:100%; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; background-color: #F4F5F7;">


		<table cellpadding="0" cellspacing="0" border="0" width="100%" style="border:0; border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; background-color: #F4F5F7;">
			<tbody>
				<tr>
					<td align="center" style="border-collapse: collapse;">

						@if ($preview)
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" style="text-align:left;">Verstuur bevestiging</h4>
						</div>
						@endif

						<!-- ROW LOGO -->
						<table cellpadding="0" cellspacing="0" border="0" width="560" style="border:0; border-collapse:collapse; background-color:#ffffff; border-radius:6px;">
							<tbody>
								<tr>
									<td style="border-collapse:collapse; vertical-align:middle; text-align center; padding:20px;">

										<!-- Headline Header -->
										<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">
											<tbody>
												@if ($user_logo)
												<tr><!-- logo -->
													<td width="100%" style="font-family: helvetica, Arial, sans-serif; font-size: 18px; letter-spacing: 0px;">
														<a href="{{ url('/') }}" style="text-decoration: none;">
															<img src="{{ url('/') }}/{{ $user_logo }}" alt="CalculatieTool.com" border="0" width="166" height="auto" style="with: 166px; height: auto; border: 5px solid #ffffff;" />
														</a>
													</td>
												</tr>
												@endif
												<tr><!-- spacer before the line -->
													<td width="100%" height="20"></td>
												</tr>
												<tr><!-- line -->
													<td width="100%" height="1" bgcolor="#d9d9d9"></td>
												</tr>
												<tr><!-- spacer after the line -->
													<td width="100%" height="30"></td>
												</tr>
												<tr>
													<td width="100%" style="font-family:helvetica, Arial, sans-serif; font-size: 14px; text-align: left; color:#8E8E8E; line-height: 24px;">
														Geachte <strong>{{ $client }}</strong>,
														@if ($preview && count($contacts)>0)
														<div style="display:inline;">
															<select id="multiple-contact" multiple="multiple">
																@foreach($contacts as $contact)
																<option value="{{ $contact->id }}">{{ $contact->getFormalName() }}</option>
																@endforeach
															</select>
														</div>
														@endif														
													</td>
												</tr>
												<tr>
													<td width="100%" height="10"></td>
												</tr>
												<tr>
													<td width="100%" style=" font-size: 14px; line-height: 24px; font-family:helvetica, Arial, sans-serif; text-align: left; color:#8E8E8E;">
														{!! nl2br($pref_email_invoice) !!}
													</td>
												</tr>
												<tr>
													<td width="100%" style="font-family:helvetica, Arial, sans-serif; font-size: 14px; text-align: left; color:#8E8E8E; line-height: 24px;">
														<br>
														<br>
															Met vriendelijke groet,
														<br>
														<br>
															{{ $user }}
														<br>
													</td>
												</tr>
											</tbody>
										</table>
										<!-- /Headline Header -->

									</td>
								</tr>
							</tbody>
						</table>
						<!-- /ROW LOGO -->

						<!-- Space -->
						<table width="100%" border="0" cellpadding="0" cellspacing="0" align="left" style="border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">
							<tbody>
								<tr>
									<td width="100%" height="30"></td>
								</tr>
							</tbody>
						</table>
						<!-- /Space -->

						<!-- ROW FOOTER -->
						<table cellpadding="0" cellspacing="0" border="0" width="560" style="border:0; border-collapse:collapse; background-color:#ffffff; border-radius:6px;">
							<tbody>
								<tr>
									<td style="border-collapse:collapse; vertical-align:middle; text-align center; padding:20px;">

										<!-- copyright-->
										<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">
											<tbody>
												<tr><!-- copyright -->
													<td width="100%" style="font-family: helvetica, Arial, sans-serif; font-size: 11px; text-align: center; line-height: 24px;">
														<center>Copyright &copy; {{ date('Y') }} CalculatieTool.com Alle rechten voorbehouden.</center>
													</td>
												</tr>
											</tbody>
										</table>
										<!-- /copyright -->


									</td>
								</tr>
							</tbody>
						</table>
						<!-- /ROW FOOTER -->

						@if ($preview)
						<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">
							<tbody>
								<tr><!-- copyright -->
									<td width="100%" style="font-family: helvetica, Arial, sans-serif; font-size: 11px; text-align: center; line-height: 24px;">
										<center>Dit is een voorbeeld van de mail die naar je opdrachtgever wordt verzonden. Jij ontvangt deze als kopie.</center>
									</td>
								</tr>
							</tbody>
						</table>

						<div class="modal-footer">
							<a class="btn btn-primary pull-right" id="sendmail" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-check"></i> Verstuur definitief naar {{ $email }}</a>
						</div>
						@endif

					</td>
				</tr>
			</tbody>
		</table>

	</body>
</html>
