const selectedColumns = new Set();
const colNames = [];
// collect column names into colNames
$(".colName").each(function(i, el) {
    colNames.push(el.textContent);
});

// >> bind events to each table cell
colNames.forEach(function(colName) {
    $("." + colName).on("click", function() {
        colNames.forEach(function(colName) {
            if ($(this).hasClass(colName)) {
                if (selectedColumns.has(colName)) {
                    selectedColumns.delete(colName);
                } else {
                    selectedColumns.add(colName);
                }
                $("." + colName).toggleClass("highlight");
            }
        }, this); // this is element from on-click event

        $("#colKeep").empty();
        colNames.forEach(function(colName) {
            if (selectedColumns.has(colName)) {
                let element = document.createElement("input");
                $(element).attr({ type: "hidden", name: "columns[]", value: colName });
                $("#colKeep").append(element);
            }
        });
        $("#colHide").empty();
        if (colNames.length != selectedColumns.size) {
            colNames.forEach(function(colName) {
                if (!selectedColumns.has(colName)) {
                    let element = document.createElement("input");
                    $(element).attr({
                        type: "hidden",
                        name: "columns[]",
                        value: colName,
                    });
                    $("#colHide").append(element);
                }
            });
        } else {
            colNames.forEach(function(colName) {
                let element = document.createElement("input");
                $(element).attr({ type: "hidden", name: "columns[]", value: colName });
                $("#colHide").append(element);
            });
        }
    });
}); // << bind function end