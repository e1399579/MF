function Player(audio, container, button, background) {
    /*对象固定属性初始化*/
    this.audio = audio;//html5的audio标签，参见HTML 音频/视频 DOM 参考手册
    this.container = container;//播放器容器
    this.button = button;//播放/暂停按钮
    this.background = background;//列表选中时的背景

    //=============↓↓自动播放所需↓↓=================
    this.list = [];//播放列表
    this.current = 0;//当前播放元素下标

    /*列表初始化为全部元素*/
    this.playlist = function() {
        var len = container.children("li").length;
        for (var i=0;i<len;i++) {
            this.list[i] = i;
        }
    }
    this.playlist();
    //=============↑↑自动播放↑↑=================

    /*给播放器注册播放事件*/
    this.play = function() {
        if (this.audio.paused) {
            this.audio.play();
            this.button.prop("class", "icon-pause");
            this.ps();
            return;
        }
        this.button.prop("class", "icon-play");
        this.audio.pause();
    }


    var pl = this;

    /*给播放/暂停按钮注册点击事件*/
    this.button.click(function(){
        pl.play();
    });

    /*播放器进度显示*/
    this.ps = function() {
        var dur = isNaN(this.audio.duration) ? 1 : this.audio.duration;
        this.progress.width(Math.floor(audio.currentTime/dur*100) + '%');
        if(!this.audio.paused)
            window.setTimeout(function(){
                pl.ps();
            }, 1000);
    }

    /*列表换背景*/
    this.bg = function(li) {
        this.container.children("li").removeClass(this.background);
        li.addClass(this.background);
    }

    /*开始*/
    this.start = function(mp3, li) {
        //当前是否选中，没选中时再改变，防止每次点击同一首重新播放
        if (!li.hasClass(this.background)) {
            this.audio.src = mp3;
            this.progress = li.children().find("div[role='progressbar']");
            this.bg(li);
        }
        this.current = li.index();
        this.play();
    }

    /*结束时随机播放其它*/
    this.audio.onended = function() {
        var index = pl.list.indexOf(pl.current);//查出当前的元素下标
        pl.list.splice(index, 1);//删除元素
        if (pl.list.length) {
            //列表为空时不再播放
            var rand = Math.round(Math.random() * (pl.list.length -1));
            var li = pl.container.children("li").eq(pl.list[rand]);
            pl.start(li.attr("data"), li);//选取未播放中随机一个播放
        } else {
            //将按钮变为播放状态
            pl.button.prop("class", "icon-play");
        }
    };
}
$(function(){
    var player = new Player($("#audio")[0], $("#player"), $("#player-btn"), "bg-info");
    var index = Math.round(Math.random() * ($("#player li").length-1));//随机
    player.start($("#player li").eq(index).attr("data"), $("#player li").eq(index));
    $("#player li").click(function(){
        player.playlist();//每次点击，列表重置
        player.start($(this).attr("data"), $(this));
    });
});

