/* select ID of 'person_b' in added relation */

let personBids = document.getElementsByName('personBid');
let personBinput = document.getElementById('personrelation-person_b_id');

personBids.forEach(function (val) {
	val.addEventListener('click', function () {
		let personb_id = val.id.slice(val.id.indexOf('-') + 1);
		personBinput.value = personb_id;
		let name = (document.querySelector('tr[data-key="' + personb_id + '"]').children)[1].innerText;
		let surname = (document.querySelector('tr[data-key="' + personb_id + '"]').children)[2].innerText;
		let outName = document.getElementById('selected-name');
		outName.innerText = name + ' ' + surname;
		console.log(name + ' ' + surname);

	})
})