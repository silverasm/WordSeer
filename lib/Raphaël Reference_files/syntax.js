(function () {
    var pres = document.getElementsByTagName("pre"),
        prel = pres.length;
    while (prel--) if (pres[prel].className == "javascript code") {
        var codes = pres[prel].getElementsByTagName("code"),
            codel = codes.length;
        while (codel--) {
            var code = codes[codel];
            var html = code.innerHTML;
            html = html.replace(/\b(abstract|boolean|break|byte|case|catch|char|class|const|continue|debugger|default|delete|do|double|else|enum|export|extends|false|final|finally|float|for|function|goto|if|implements|import|in|instanceof|int|interface|long|native|new|null|package|private|protected|public|return|short|static|super|switch|synchronized|this|throw|throws|transient|true|try|typeof|var|void|volatile|while|with|undefined)\b/g, "<b>$1</b>")
            .replace(/("[^"]*?(?:\\"[^"]*?)*")/g, "<i>$1</i>")
            .replace(/( \= | \- | \+ | \* | \&\& | \|\| | \/ | == | === )/g, '<span class="s">$1</span>')
            .replace(/(\b(0[xX][\da-fA-F]+)|((\.\d+|\b\d+(\.\d+)?)(?:e[-+]?\d+)?))\b/g, '<span class="d">$1</span>')
            .replace(/(\/\/.*?(?:\n|$)|\/\*(?:.|\s)*?\*\/)$/gm, '<span class="c">$1</span>');
            code.innerHTML = html;
        }
    }
})();