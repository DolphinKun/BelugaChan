function Helper() {
    document.getElementById("helper").innerHTML = '<div class="assistance_helper_border"><p>Welcome to Dolphin Helper. Dolphin Helper is designed to make shitposting easier.</p><br><button onclick="CreateShitPost();">Use dolphin system power to calculate the best shitpost for you</button></div>';
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function CreateShitPost() {
    // Do "loading"
    document.getElementById("helper").innerHTML = '<div class="assistance_helper assistance_helper_border"><p>Please wait... <img src="../img/loading.gif"></p></div>';
    await sleep(2000);
    // Say we're doing the shitpost
    document.getElementById("helper").innerHTML = '<div class="assistance_helper assistance_helper_border"><p>Calculating the area of a dolphin... <img src="../img/loading.gif"></p></div>';
    await sleep(2000);
    // Oops
    document.getElementById("helper").innerHTML = '<div class="assistance_helper assistance_helper_border"><p>Weird. This dolphin seems to be past 10 metres in size. Attempting to repair... <span style="font-size:50px;">&#9888;</span></p></div>';
    await sleep(3000);
    // Somehow survives
    document.getElementById("helper").innerHTML = '<div class="assistance_helper assistance_helper_border"><p>Regenerating shitpost. Hold tight! <img src="../img/loading.gif"></p></div>';
    await sleep(2000);
    // Wooo! We did it
    document.getElementById("helper").innerHTML = '<div class="assistance_helper assistance_helper_border"><p>Your shitpost was created, only to be stolen by a shark. It\'s very sad news :(</p><button onclick="document.getElementById(\'helper\').style.display = \'none\';">Close helper</button></div>';

}