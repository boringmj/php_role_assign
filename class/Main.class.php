<?php

class Main
{

    protected $data=array();

    function __construct()
    {
        if($this->check())
        {
            $this->getData();
            $this->getReturn();
        }
    }

    function check()
    {
        /* 这里是废弃的代码,因为没必要检测环境是否支持这些函数
        if(!function_exists('session_start'))
        {
            $this->return="系统错误 -1<br>当前环境不支持SESSION";
            return 0;
        }
        if(!class_exists('PDO'))
        {
            $this->return="系统错误 -1<br>当前环境不支持PDO";
            return 0;
        }
        session_start();
        */
        return 1;
    }

    protected function getData()
    {
        //数据来源通过cookie获取(反正是demo,怎么节约资源怎么来)
        if(empty($_COOKIE['data']))
        {
            $this->data=array(
                'user'=>array(1,1,1,1,1),       //玩家期望点数,最低为1或至少不能全部为0(下文中用v表示)
                'count'=>0,                     //总参与次数
                'hit'=>0,                       //本机制命中次数
                'normal_hit'=>0,                //正常随机命中次数
                'add'=>2                        //参与每轮增加期望点数
            );
            echo "系统检测到您是第一次进行测试,如果您在一段时间内多次看到该警告请检查您的浏览器是否支持<u>cookie</u><br>";
            $_COOKIE['data']=json_encode($this->data);
        }
        //没有验证json是否符合规范,怎么简单怎么来
        $this->data=json_decode($_COOKIE['data']);
    }

    protected function getReturn()
    {
        //计算出总点数S(每一个玩家的v相加)
        $S=0;
        foreach($this->data->user as $v)
        {
            $S+=$v;
        }
        //获取随机的R值(范围为1-S,最低为0可以解决玩家期望点数全部为0的问题,但是相对的第一位玩家的概率就会提升1点,所以不推荐)
        $R=rand(1,$S);
        $R1=$R;
        //取得一般结果
        $R2=rand(0,count($this->data->user));
        //得出区间属于哪位玩家P
        $P;
        foreach($this->data->user as $P1=>$v)
        {
            if(($R1-=$v)<=0)
            {
                $P=$P1;
                break;
            }
        }
        //最后处理剩余数据(也可以放到上面处理,放下边处理主要是思路清晰一点)
        $data1=$this->data->user;
        $F=round($this->data->user[$P]/$S,6)*100;  //命中的玩家在本轮的命中率
        $F3=round($this->data->user[0]/$S,6)*100;  //您的的玩家在本轮的命中率
        foreach($this->data->user as $P1=>$v)
        {
            if($P1!=$P)
            {
                $this->data->user[$P1]+=$this->data->add;
            }
            else
            {
                //这里需要注意的是初始值是1
                $this->data->user[$P1]=1;
            }
        }
        if($P==0)
        {
            $this->data->hit+=1;
        }
        if($R2==0)
        {
            $this->data->normal_hit+=1;
        }
        $this->data->count+=1;
        //命中的玩家在本轮的命中率
        if($this->data->hit==0)
        {
            $F1=round(0,6);
        }
        else
        {
            $F1=round($this->data->hit/$this->data->count,6)*100;
        }
        //命中的玩家在本轮的命中率
        if($this->data->normal_hit==0)
        {
            $F2=round(0,6);
        }
        else
        {
            $F2=round($this->data->normal_hit/$this->data->count,6)*100;
        }
        //这里输出最终结果
        setcookie("data",json_encode($this->data));
        echo "
            调试参数：S={$S},R={$R},R2={$R2}<br>
            注意:起始玩家是0,您的玩家为玩家0<br><br>
            本轮命中玩家：玩家{$P}<br>
            本轮命中的玩家命中率：{$F}%<br>
            本轮您的玩家命中率：{$F3}%<br>
            总进行{$this->data->count}轮<br>
            本机制玩家总命中{$this->data->hit}(总命中率：{$F1}%)轮<br>
            一般机制玩家总命中{$this->data->normal_hit}(总命中率：{$F2}%)轮<br><br>
            本轮玩家期望点数:<br>
        ";
        foreach($data1 as $P1=>$v)
        {
            $F4=round($v/$S,6)*100;
            echo "玩家{$P1}有{$v}点(命中率：{$F4}%)&nbsp;&nbsp;&nbsp;";
        }
        echo "<br>下轮玩家期望点<br>";
        foreach($this->data->user as $P1=>$v)
        {
            echo "玩家{$P1}有{$v}点&nbsp;&nbsp;&nbsp;";
        }
    }

}

?>