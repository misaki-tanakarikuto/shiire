const App1 = {
	data() {
		return {
			count: 0,
			seen: true,
			class1: "red",
			todos: [
				{ text: 'PHP' },
				{ text: 'Ruby' },
				{ text: 'Python' },
			],
			varString: "Hello!",
			itemList: [
				{id: 0, text: '0番'},
				{id: 1, text: '1番'},
				{id: 2, text: '2番'}
			]
		}
	},
	methods: {
		increment: function () {
			this.count += 1
		},
		off: function () {
			this.seen = false
		}
	}
}
const app1 = Vue.createApp(App1)

app1.component('template1', {
	props: ['prop1'],
	template: `
		<button>{{prop1}}</button>
		<br>
	`
})

app1.component('template2', {
	props: ['prop2', 'prop3'],
	template: `
		<li>{{ prop2.id }}は{{ prop2.text }}{{ prop3 }}</li>
	`
})

app1.mount('#app')
