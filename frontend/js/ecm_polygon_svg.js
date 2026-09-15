// top 33.3
// bottom 334.8

class ECM_POLYGON_SVG {
    constructor(width, height, col_pri, col_sec, level, data) {
        this.width = width;
        this.height = height;
        this.col_pri = col_pri;
        this.col_sec = col_sec;
        this.level = level;
        this.data = data;

        this.center = [width / 2, height / 2 + 15.95];
        this.max_radius = (Math.min(this.width, this.height)) / 2;
        this.numberOfCorners = data.length;
    }

    getHTML() {
        // get lines
        let linePath_arr = [];
        for (let i = 1; i <= this.level; i++) {
            linePath_arr.push(this.getPath(this.getPolygonLine(this.max_radius * (i / (this.level + 1))), 'fill="none" stroke="black" stroke-width="1"'));
        }

        let lable_points = this.getPolygonLine(this.max_radius * (1 - (0 / this.level)));
        //linePath_arr.push(this.getLable(lable_points, 'fill="#444" font-size="12px" font-family="Arial" text-anchor="middle"'));


        let article_points = this.getPolygon();
        linePath_arr.push(this.getPath(article_points, 'fill="#ff4c4c" fill-opacity="0.4" stroke="#ff4c4c" stroke-width="1"'));
        linePath_arr.push(this.getButtons(article_points, 'fill="#ff4c4c" fill-opacity="0.4" stroke="#ff4c4c" stroke-width="1"'));
        
        
        return this.getSVG(linePath_arr, 'viewBox="0 0 '+ this.width +' ' + this.height + '"')

    }

    getPolygonLine(radius) {
        var angle = 0;
        let startAngle = Math.PI / 2;

        var points = [];

        angle = startAngle;
        while (angle - startAngle < 2 * Math.PI) {

            let x = radius * Math.cos(angle) + this.center[0]; // transform polar coordinates to XY
            let y = -radius * Math.sin(angle) + this.center[1];
            let point = [x, y];
            points.push(point);

            let angleIncrease = (2.0 * Math.PI) / this.numberOfCorners;
            angle += angleIncrease;
        }
        return points;
    }

    getPolygon() {
        var angle = 0;
        let startAngle = Math.PI / 2;

        let max_radius = this.max_radius;
        let level = this.level;
        let center = this.center;
        let numberOfCorners = this.numberOfCorners;

        var points = [];

        angle = startAngle;
        this.data.forEach(function (element, index, array) {
            let radius = (max_radius * (1 - (1/(level+1)) )) * (element[2] / element[3]);
            let x = radius * Math.cos(angle) + center[0]; // transform polar coordinates to XY
            let y = -radius * Math.sin(angle) + center[1];
            let point = [x, y];
            points.push(point);

            let angleIncrease = (2.0 * Math.PI) / numberOfCorners;
            angle += angleIncrease;
        });
        return points;
    }

    getPath(point_arr, style = '') {
        let path = '<path d="';
        point_arr.forEach(function (element, index, array) {
            if (index == 0) {
                path = path + "M";
            } else {
                path = path + "L";
            }
            path = path + element[0] + " " + element[1] + " ";
            if (index == array.length - 1) {
                path = path + "Z";
            }
        });
        return path + '" ' + style + ' />';
    }

    getButtons(point_arr, style = '') {
        let buttons = '<g class="ecm_buttons">';
        
        let outerline_grid = this.getPolygonLine(this.max_radius * (this.level / (this.level + 1)));
        let outerline = this.getPolygonLine(this.max_radius * (this.level / (this.level)));
        for (let i = 0; i < this.numberOfCorners; i++) { // i < this.numberOfCorners;
            let outerline_points = this.getButtonPoints(outerline, this.center, i, this.numberOfCorners);
            
            let obj = {"title":this.data[i][0],"description":this.data[i][1], "value":this.data[i][2], "max_value":this.data[i][3]};

            buttons = buttons + '<g class="ecm_button" style="" attr-ecm-svg='+JSON.stringify(obj)+'>';
            buttons = buttons + this.getPath(this.getButtonPoints(outerline_grid, this.center, i, this.numberOfCorners), 'class="ecm_button_vis" fill="grey" stroke="grey" stroke-width="1"');
            buttons = buttons + this.getPath(outerline_points, 'opacity="0"');
            buttons = buttons + '<text x="' + outerline_points[2][0] + '" y="' + outerline_points[2][1] + '" ' + 'text-anchor="middle"' + '><tspan alignment-baseline="middle">' + this.data[i][0] + '</tspan></text>';
            buttons = buttons + '</g>';
        }
        return buttons + '</g>';
    }
    
    getButtonPoints(outerline, center,i, numberOfCorners) {
        let i_prev = (i + 1) % (numberOfCorners);
        let i_next = i - 1;
        if (i_next < 0) {
            i_next = numberOfCorners - 1;
        }
            
        let a = [outerline[i][0] + (outerline[i_next][0] - outerline[i][0]) / 2, outerline[i][1] + (outerline[i_next][1] - outerline[i][1]) / 2];
        let b = [outerline[i][0], outerline[i][1]];
        let c = [outerline[i][0] - (outerline[i][0] - outerline[i_prev][0]) / 2, outerline[i][1] - (outerline[i][1] - outerline[i_prev][1]) / 2];
        
        return [center, a, b, c];
    }

    getLable(point_arr, style = '') {
        let lables = '<g class="lable">';
        point_arr.forEach(function (element, index, array) {
            lables = lables + '<text x="' + element[0] + '" y="' + element[1] + '" ' + style + '><tspan alignment-baseline="middle">TEXT</tspan></text>';
        });
        return lables + '</g>';
    }

    getSVG(path_arr, style = '') {
        let svg = '<svg ' + style + '>';
        path_arr.forEach(function (path) {
            svg = svg + path;
        });
        return svg + '</svg>';
    }
}


