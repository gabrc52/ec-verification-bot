import { Client, Events, GatewayIntentBits, Snowflake } from 'discord.js';
import { readFileSync } from 'fs';
import config = require('./config');

const client = new Client({
    intents: [
        // TODO: adding all intents for now
        // remove unnecessary ones
        GatewayIntentBits.Guilds,
		GatewayIntentBits.GuildMessages,
		GatewayIntentBits.MessageContent,
		GatewayIntentBits.GuildMembers,
    ],
});

const modules = ['verification'];

client.once(Events.ClientReady, c => {
    for (const module of modules) {
        require('./' + module).setup(client, config);
    }
    console.log(`Ready! Logged in as ${c.user.tag}`);
});

/// DM after successful verification
client.on(Events.GuildMemberUpdate, (oldMember, newMember) => {
    const guild = client.guilds.cache.get(config.guild_2027);
    const wasGivenRole = (role: Snowflake) => !oldMember.roles.cache.get(role) && newMember.roles.cache.get(role);
    if (newMember.guild == guild && wasGivenRole(config.admitted_role_2027)) {
        newMember.send(config.post_verification_dm);
    }
});

const token = readFileSync('token.txt', 'utf-8');

client.login(token);