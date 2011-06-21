var punctuation = [" "];
//var punctuation = [" ", "!", "?", ":", "$", "$", "%", "^", "&", "*", "(", ")", "{","}", '"', "'", "!",".", ",", ";", "@", "/", "<", ">","|", "\\", "=", "+", "-", "_", "?", ":", "", "", "%", "^", "&", "*", "(", ")", "{","}", "[","]"];

function tokenize(sentence){
	var words = [];
	word = "";
	for(var i = 0; i< sentence.length; i++){
		if(contains(punctuation, sentence[i])){
			words.push(word);
			if(sentence[i]!=" "){
				words.push(sentence[i]);
			}
			word = "";
		}else{
			word += sentence[i];
		}
	}
	return words;
}