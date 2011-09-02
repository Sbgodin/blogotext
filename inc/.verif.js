function verif() 
{

	var Errors = '';
	var comForm = document.getElementById("form-commentaire");

		var Pseudo  = comForm.auteur;
		if (Pseudo.value != "") {
			Pseudo.style.backgroundColor = "";
			PsError = false;
		}
		else {
			PsError = true;
			Pseudo.style.backgroundColor = "#fba";
			Errors = Errors + "Pseudo non valide !\n"
		}

		var Email   = comForm.email;
		var deForm = /^[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/;
		if (deForm.test(Email.value)) {
			Email.style.backgroundColor = "";
			EmError = false;
		}
		else {
			EmError = true;
			Email.style.backgroundColor = "#fba";
			Errors = Errors + "Email non valide !\n"
		}

		var Message = comForm.commentaire;
		if (Message.value != "") {
			Message.style.backgroundColor = "";
			MeError = false;
		}
		else {
			MeError = true;
			Message.style.backgroundColor = "#fba";
			Errors = Errors + "Votre Message est vide !\n";
		}

		var Captcha = comForm.captcha;
		var CaptchForm = /^[0-9]+$/;
		if (CaptchForm.test(Captcha.value)) {
			Captcha.style.backgroundColor = "";
			CaError = false;
		}
		else {
			CaError = true;
			Captcha.style.backgroundColor = "#fba";
			Errors = Errors + "Le code de vérification doit être remplie en chiffres !\n";
		}

	if (Errors)
		{
		alert(Errors);
		return false;
		}
	else 
		return true;
}

