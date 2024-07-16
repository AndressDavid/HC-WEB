
export  class ConfigEnvironment{
    private config = {

    environmentPhp:{
        environment: "local1",
        environment_list: ["produccion", "desarrollo", "local1", "local2"]
    },

    environmentApiPhp:{
        environment: "local1",
        environment_list: ["produccion", "desarrollo", "local1", "local2"]
    }}

    constructor(
    ){}

    public getConfig():any{
        return this.config;
    }
}