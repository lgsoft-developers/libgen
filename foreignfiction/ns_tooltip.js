//	ns_tooltip.js
//	created by Nikita Seleckis
//	www.seleckis.lv

var l = 0, t = 0
var IE = document.all?true:false
document.onmousemove = getMouseXY
var ns_tt = document.createElement("div")
function getMouseXY(e) {
	if (IE) {
		l = event.clientX + document.body.scrollLeft
		t = event.clientY + document.body.scrollTop
	}
	else {
		l = e.pageX
		t = e.pageY
	}  
	ns_tt.style.left = l + "px"
	ns_tt.style.top = t + "px"
	return true
}

function AddTT(tt_text){
	document.body.appendChild(ns_tt)
	ns_tt.id = "ns_tt"
	ns_tt.innerHTML = tt_text
}

function RemoveTT() {
	document.body.removeChild(document.getElementById("ns_tt"))
}
