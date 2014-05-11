crLang = {
	aLangs: [],
	
	line: function(string) {
		if (this.aLangs[string] != null) {
			return this.aLangs[string];
		}
		return string;
	}
};
