<?php

class Main
{
    /** 简化的类
     * 相比Main.class.php更加简洁
     * 对了,$this->data->user里面有多少个元素就有多少个玩家
    */

    protected $data=array();

    function __construct()
    {
        $this->getData();
        $this->getReturn();
    }

    protected function getData()
    {
        //数据来源通过cookie获取(反正是demo,怎么节约资源怎么来)
        if(empty($_COOKIE['data']))
        {
            $this->data=array(
                'user'=>array(1,1,1,1,1),       //玩家期望点数,最低为1或至少不能全部为0(下文中用v表示)
                'add'=>2                        //参与每轮增加期望点数
            );
            $_COOKIE['data']=json_encode($this->data);
        }
        //没有验证json是否符合规范,怎么简单怎么来
        $this->data=json_decode($_COOKIE['data']);
    }

    protected function getReturn()
    {
        $S=0;
        foreach($this->data->user as $v)
            $S+=$v;
        $R=rand(1,$S);
        foreach($this->data->user as $P1=>$v)
        {
            if($R>0&&($R-=$v)<=0)
            {
                $this->data->user[$P1]=1;
                echo "本次命中玩家{$P1}<br>";
            }
            else
                $this->data->user[$P1]+=$this->data->add;
        }
        //存储数据
        setcookie("data",json_encode($this->data));
    }

}

?>