Vue.filter('strlimit', function(value, length) {
    if (value.toString().length <= length) {
        return value;
    }

    return value.toString().substring(0, length) + '...';
});